<?php

namespace App\Http\Controllers;

use App\Models\LegalPage;
use App\Services\LegalPageRenderer;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LegalController extends Controller
{
    /**
     * Templates this feature replaces. Kept as a fallback until they are deleted
     * one release after the CMS ships.
     *
     * @var array<string, string>
     */
    private const LEGACY_VIEWS = [
        LegalPage::TYPE_TERMS => 'web.public.legal.terms-of-service',
        LegalPage::TYPE_PRIVACY => 'web.public.legal.privacy-policy',
        LegalPage::TYPE_DATA_SHARING => 'web.public.legal.data-sharing-policy',
    ];

    public function __construct(private readonly LegalPageRenderer $renderer) {}

    public function termsOfService(): View
    {
        return $this->show(LegalPage::TYPE_TERMS);
    }

    public function privacyPolicy(): View
    {
        return $this->show(LegalPage::TYPE_PRIVACY);
    }

    public function dataSharingPolicy(): View
    {
        return $this->show(LegalPage::TYPE_DATA_SHARING);
    }

    private function show(string $type): View
    {
        $page = LegalPage::current($type);

        if ($page) {
            return view('web.public.legal.show', [
                'title' => $page->title,
                'body' => $this->renderer->render($page),
                'effectiveDate' => $page->effective_date,
            ]);
        }

        return $this->showFallback($type);
    }

    /**
     * Fallback for an install whose legal_pages table has no published version for
     * this document — a fresh install, or one where the import migration could not
     * read the legacy templates.
     *
     * While the legacy templates still ship (they are removed a release after the
     * CMS lands) they are the first fallback, so this page can never regress to a
     * 404 for an installation that was serving it before the upgrade.
     */
    private function showFallback(string $type): View
    {
        $legacyView = self::LEGACY_VIEWS[$type] ?? null;

        if ($legacyView && view()->exists($legacyView)) {
            return view($legacyView);
        }

        return $this->showStub($type);
    }

    /**
     * Last-resort fallback once the legacy templates are gone: the shipped stub,
     * rendered through the same token pipeline as database content so it never
     * shows raw {{placeholder}} text.
     */
    private function showStub(string $type): View
    {
        $locales = array_unique([
            app()->getLocale(),
            config('app.fallback_locale', 'en'),
            'en',
        ]);

        foreach ($locales as $locale) {
            $path = database_path("seeders/data/legal/{$locale}/{$type}.html");

            if (is_file($path)) {
                return view('web.public.legal.show', [
                    'title' => __("legal.stub_title.{$type}"),
                    'body' => $this->renderer->renderBody(file_get_contents($path) ?: ''),
                    'effectiveDate' => null,
                ]);
            }
        }

        throw new NotFoundHttpException("No legal page available for type [{$type}].");
    }
}
