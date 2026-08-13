<?php

use App\Models\LegalPage;
use App\Services\LegalHtmlSanitizer;
use App\Services\LegalPageRenderer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;

/**
 * Imports an installation's existing legal pages into the CMS.
 *
 * Editing lang/{locale}/legal.php (or the legal Blade views) was previously the only
 * way for a federation to change its published legal text. Those files are part of
 * the install, so this migration renders them as they stand on THIS deployment and
 * stores the result — a federation that customized its terms keeps exactly its own
 * wording, with no manual step.
 *
 * This is why the legacy views and lang files are not deleted in the same release
 * that adds the CMS: a migration cannot read files its own deploy has removed.
 *
 * Idempotent: any (type, locale) that already has a row is left untouched, so it is
 * safe to re-run and safe in either order against LegalPageSeeder.
 */
return new class extends Migration
{
    /** @var array<string, string> type => legacy view */
    private const LEGACY_VIEWS = [
        LegalPage::TYPE_TERMS => 'web.public.legal.terms-of-service',
        LegalPage::TYPE_PRIVACY => 'web.public.legal.privacy-policy',
        LegalPage::TYPE_DATA_SHARING => 'web.public.legal.data-sharing-policy',
    ];

    public function up(): void
    {
        $sanitizer = app(LegalHtmlSanitizer::class);
        $detokenize = app(LegalPageRenderer::class)->detokenize();
        $originalLocale = App::getLocale();
        $imported = 0;

        foreach (array_keys(self::LEGACY_VIEWS) as $type) {
            if (! View::exists(self::LEGACY_VIEWS[$type])) {
                continue;
            }

            foreach (config('app.locales', []) as $locale) {
                if ($this->alreadyImported($type, $locale)) {
                    continue;
                }

                App::setLocale($locale);

                $extracted = $this->extractLegacyContent($type, $locale);

                if ($extracted === null) {
                    continue;
                }

                DB::table('legal_pages')->insert([
                    'type' => $type,
                    'locale' => $locale,
                    'version' => 1,
                    'title' => $extracted['title'],
                    'body' => strtr($sanitizer->sanitize($extracted['body']), $detokenize),
                    'effective_date' => $extracted['effective_date'],
                    'published_at' => now(),
                    'created_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $imported++;
            }
        }

        App::setLocale($originalLocale);

        if ($imported > 0) {
            info("Imported {$imported} legal page(s) from the installation's existing templates.");
        }
    }

    public function down(): void
    {
        DB::table('legal_pages')->where('version', 1)->delete();
    }

    private function alreadyImported(string $type, string $locale): bool
    {
        return DB::table('legal_pages')
            ->where('type', $type)
            ->where('locale', $locale)
            ->exists();
    }

    /**
     * Render the legacy template for the active locale and pull out the content
     * block, separating the heading and "last update" line from the body.
     *
     * Deliberately compiles only the template's own markup instead of rendering
     * the view through its layout. The public layout pulls in @vite, which throws
     * when the frontend assets have not been built — and `php artisan migrate`
     * routinely runs before `npm run build` on a deploy (CI never builds at all).
     * Rendering the full page here would make the import silently skip everything
     * in exactly the situations it exists to cover.
     *
     * @return array{title: string, body: string, effective_date: ?string}|null
     */
    private function extractLegacyContent(string $type, string $locale): ?array
    {
        try {
            $html = Blade::render($this->templateBody($type));
        } catch (\Throwable $e) {
            report($e);
            info("Skipped legal import for {$type}/{$locale}: " . $e->getMessage());

            return null;
        }

        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"?>' . $html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($document);
        $content = $xpath->query('//div[contains(@class, "max-w-4xl")]')->item(0);

        if (! $content) {
            return null;
        }

        $title = '';
        $effectiveDate = null;

        // The heading and the "Last update: dd/mm/yyyy" line become columns rather
        // than body content, so the new template owns their presentation.
        foreach (iterator_to_array($xpath->query('.//h1', $content)) as $heading) {
            $title = trim($heading->textContent);
            $heading->parentNode?->removeChild($heading);
            break;
        }

        foreach (iterator_to_array($xpath->query('.//p[contains(@class, "text-gray-500")]', $content)) as $paragraph) {
            if (preg_match('/(\d{2})\/(\d{2})\/(\d{4})/', $paragraph->textContent, $matches)) {
                $effectiveDate = "{$matches[3]}-{$matches[2]}-{$matches[1]}";
                $paragraph->parentNode?->removeChild($paragraph);
            }
            break;
        }

        $body = '';
        foreach ($content->childNodes as $child) {
            $body .= $document->saveHTML($child);
        }

        if (trim(strip_tags($body)) === '') {
            return null;
        }

        return [
            'title' => $title !== '' ? $title : $this->fallbackTitle($type),
            'body' => $body,
            'effective_date' => $effectiveDate,
        ];
    }

    /**
     * The legacy template's markup with its layout wrapper removed, so it can be
     * compiled standalone. The remaining markup only uses __() and config(), which
     * need nothing beyond a booted application.
     */
    private function templateBody(string $type): string
    {
        $path = resource_path('views/' . str_replace('.', '/', self::LEGACY_VIEWS[$type]) . '.blade.php');
        $source = (string) file_get_contents($path);

        return preg_replace(
            [
                '/@section\([^\n]*\)\s*/',          // page title directive
                '/<\/?x-public-layout[^>]*>/',      // layout wrapper
                '/<x-brand-logo[^>]*\/?>/',         // logo component (asset dependent)
            ],
            '',
            $source
        ) ?? $source;
    }

    private function fallbackTitle(string $type): string
    {
        $key = match ($type) {
            LegalPage::TYPE_TERMS => 'legal.terms_of_use',
            LegalPage::TYPE_PRIVACY => 'legal.privacy_policy',
            default => 'legal.data_sharing_policy',
        };

        return Lang::has($key) ? __($key) : $type;
    }
};
