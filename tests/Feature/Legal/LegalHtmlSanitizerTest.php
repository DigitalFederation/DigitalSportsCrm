<?php

use App\Services\LegalHtmlSanitizer;

/**
 * The security boundary for admin-authored legal page content.
 *
 * Legal page bodies are stored as HTML and rendered with {!! !!}, so everything
 * that protects the public page happens here. These cases are the payload corpus:
 * if one of them starts failing, stored XSS is live on a public page.
 */
beforeEach(function () {
    $this->sanitizer = new LegalHtmlSanitizer();
});

dataset('dangerous payloads', [
    'script tag' => ['<p>ok</p><script>alert(1)</script>', 'alert(1)'],
    'inline event handler' => ['<p onclick="alert(1)">text</p>', 'onclick'],
    'img onerror' => ['<img src=x onerror=alert(1)>', 'onerror'],
    'svg script' => ['<svg><script>alert(1)</script></svg>', 'alert(1)'],
    'iframe' => ['<iframe src="https://evil.test"></iframe>', 'iframe'],
    'style attribute' => ['<p style="position:fixed">x</p>', 'style='],
    'style tag' => ['<style>body{display:none}</style><p>x</p>', '<style'],
    'javascript href' => ['<a href="javascript:alert(1)">x</a>', 'javascript:'],
    'javascript href mixed case' => ['<a href="JaVaScRiPt:alert(1)">x</a>', 'javascript:'],
    'javascript href entity encoded' => ['<a href="&#106;avascript:alert(1)">x</a>', 'javascript:'],
    'data uri href' => ['<a href="data:text/html;base64,PHNjcmlwdD4=">x</a>', 'data:'],
    'object tag' => ['<object data="evil.swf"></object>', '<object'],
    'form injection' => ['<form action="https://evil.test"><input name="p"></form>', '<form'],
    'meta refresh' => ['<meta http-equiv="refresh" content="0;url=https://evil.test">', '<meta'],
    'base tag' => ['<base href="https://evil.test/">', '<base'],
]);

test('strips dangerous markup', function (string $payload, string $forbidden) {
    $clean = $this->sanitizer->sanitize($payload);

    expect(strtolower($clean))->not->toContain(strtolower($forbidden));
})->with('dangerous payloads');

test('keeps the legitimate formatting a legal page needs', function () {
    $clean = $this->sanitizer->sanitize(
        '<h2>Section</h2>'
        . '<p>Text with <strong>bold</strong> and <em>italic</em>.</p>'
        . '<ul><li>bullet</li></ul>'
        . '<ol><li>numbered</li></ol>'
        . '<blockquote>quoted</blockquote>'
    );

    expect($clean)
        ->toContain('<h2>')
        ->toContain('<strong>')
        ->toContain('<em>')
        ->toContain('<ul>')
        ->toContain('<ol>')
        ->toContain('<blockquote>')
        ->toContain('Section')
        ->toContain('bullet');
});

test('keeps safe links and hardens them', function () {
    $clean = $this->sanitizer->sanitize('<a href="https://example.test">link</a>');

    expect($clean)
        ->toContain('href="https://example.test"')
        ->toContain('rel="noopener noreferrer nofollow"');
});

test('keeps mailto links', function () {
    expect($this->sanitizer->sanitize('<a href="mailto:dpo@example.test">mail</a>'))
        ->toContain('mailto:dpo@example.test');
});

test('keeps the words when it removes an unsafe link', function () {
    $clean = $this->sanitizer->sanitize('<a href="javascript:alert(1)">important notice</a>');

    expect($clean)
        ->toContain('important notice')
        ->not->toContain('javascript:');
});

test('strips schemes smuggled past a naive check with whitespace or control characters', function () {
    foreach (['java\tscript:alert(1)', "java\nscript:alert(1)", " javascript:alert(1)"] as $href) {
        $clean = $this->sanitizer->sanitize('<a href="' . $href . '">x</a>');

        expect(str_contains(strtolower(preg_replace('/\s/', '', $clean) ?? ''), 'javascript:'))
            ->toBeFalse("Payload survived: {$href}");
    }
});

test('handles empty and null input', function () {
    expect($this->sanitizer->sanitize(null))->toBe('')
        ->and($this->sanitizer->sanitize(''))->toBe('')
        ->and($this->sanitizer->sanitize('   '))->toBe('');
});

test('is idempotent', function () {
    $once = $this->sanitizer->sanitize('<h2>T</h2><p>x <a href="https://a.test">l</a></p><script>alert(1)</script>');
    $twice = $this->sanitizer->sanitize($once);

    expect($twice)->toBe($once);
});
