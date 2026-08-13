<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Tiptap\Editor;
use Tiptap\Extensions\StarterKit;
use Tiptap\Marks\Link;

/**
 * Sanitizes admin-authored legal page HTML.
 *
 * Two independent passes, because neither is sufficient alone:
 *
 *  1. Tiptap parses the HTML into a document and re-renders it, keeping only nodes
 *     and attributes its schema knows about. This structurally removes <script>,
 *     <iframe>, <img onerror>, style attributes and on* handlers — they simply have
 *     nowhere to live in the document model.
 *
 *  2. A link policy pass. Tiptap's Link mark does NOT validate href schemes: it
 *     passes `javascript:` and `data:` URLs straight through, and it decodes HTML
 *     entities, so `&#106;avascript:` becomes a working `javascript:` URL. Pass 2
 *     enforces a scheme allow-list on the re-rendered output, which is the point at
 *     which any such decoding has already happened.
 *
 * Only the output of both passes is ever persisted, so the public page can render
 * the stored body directly.
 */
class LegalHtmlSanitizer
{
    /** Schemes a legal page may link to. */
    private const ALLOWED_SCHEMES = ['http', 'https', 'mailto'];

    /**
     * Tags kept when the parser cannot build a document at all. Deliberately
     * narrower than the editor's toolbar: this path only runs on malformed input.
     */
    private const FALLBACK_ALLOWED_TAGS = '<p><br><strong><em><u><h2><h3><h4><ul><ol><li><a><blockquote><hr>';

    public function sanitize(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        $editor = new Editor([
            'extensions' => [
                new StarterKit(),
                new Link(['openOnClick' => false]),
            ],
        ]);

        try {
            $parsed = $editor->setContent($html)->getHTML();
        } catch (\Throwable $e) {
            // Tiptap's DOM parser throws on input that yields no <body> — a bare
            // <meta>, <base> or comment-only document will do it. Fail CLOSED: fall
            // back to a conservative tag allow-list rather than letting the original
            // markup through or surfacing a 500 from a public save path.
            return $this->enforceLinkPolicy(
                strip_tags($html, self::FALLBACK_ALLOWED_TAGS)
            );
        }

        return $this->enforceLinkPolicy($parsed);
    }

    /**
     * Drop the href of any anchor pointing somewhere other than an allow-listed
     * scheme, keeping its text, and normalize rel/target on the survivors.
     */
    private function enforceLinkPolicy(string $html): string
    {
        if (! str_contains($html, '<a ')) {
            return $html;
        }

        $document = new DOMDocument();

        $previous = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="UTF-8"?><div id="legal-root">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($document);

        /** @var DOMElement $anchor */
        foreach ($xpath->query('//a') as $anchor) {
            $href = trim($anchor->getAttribute('href'));

            if ($this->isAllowedHref($href)) {
                $anchor->setAttribute('rel', 'noopener noreferrer nofollow');
                $anchor->setAttribute('target', '_blank');

                continue;
            }

            // Unsafe or unusable link: keep the words, lose the link.
            $anchor->removeAttribute('href');
            $anchor->removeAttribute('target');
            $anchor->removeAttribute('rel');
        }

        $root = $document->getElementById('legal-root');

        if (! $root) {
            return $html;
        }

        $inner = '';
        foreach ($root->childNodes as $child) {
            $inner .= $document->saveHTML($child);
        }

        return $inner;
    }

    private function isAllowedHref(string $href): bool
    {
        if ($href === '') {
            return false;
        }

        // Strip control characters and whitespace that can be used to smuggle a
        // scheme past a naive check (e.g. "java\tscript:").
        $normalized = strtolower(preg_replace('/[\s\x00-\x1F\x7F]/', '', $href) ?? '');

        if ($normalized === '') {
            return false;
        }

        // Relative and anchor links carry no scheme and are safe by construction.
        if (str_starts_with($normalized, '/') || str_starts_with($normalized, '#')) {
            return true;
        }

        foreach (self::ALLOWED_SCHEMES as $scheme) {
            if (str_starts_with($normalized, $scheme . ':')) {
                return true;
            }
        }

        return false;
    }
}
