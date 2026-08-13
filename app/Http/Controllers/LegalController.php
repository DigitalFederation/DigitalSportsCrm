<?php

namespace App\Http\Controllers;

use App\Models\LegalPage;
use App\Services\LegalPageRenderer;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LegalController extends Controller
{
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

        return $this->showStub($type);
    }

    /**
     * Last-resort fallback for an install whose legal_pages table is empty (for
     * example a fresh install that has not been seeded yet). The shipped stub is
     * rendered through the same token pipeline as database content, so it never
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
