<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LegalPageRequest;
use App\Models\LegalPage;
use App\Services\LegalPageRenderer;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LegalPageController extends Controller
{
    public function __construct(private readonly LegalPageRenderer $renderer) {}

    public function index(): View
    {
        $pages = LegalPage::query()
            ->orderBy('type')
            ->orderBy('locale')
            ->orderByDesc('version')
            ->get()
            ->groupBy(['type', 'locale']);

        return view('web.admin.legal-pages.index', [
            'pages' => $pages,
            'types' => LegalPage::types(),
            'locales' => config('app.locales', []),
            'localeLabels' => config('app.available_locales', []),
        ]);
    }

    public function edit(string $type, string $locale): View
    {
        abort_unless(in_array($type, LegalPage::types(), true), 404);
        abort_unless(in_array($locale, config('app.locales', []), true), 404);

        $draft = LegalPage::draftFor($type, $locale);
        $current = LegalPage::currentForLocale($type, $locale);

        return view('web.admin.legal-pages.edit', [
            'type' => $type,
            'locale' => $locale,
            'draft' => $draft,
            'current' => $current,
            // Editing starts from the open draft, else a copy of what is live.
            'page' => $draft ?? $current,
            'tokens' => array_keys($this->renderer->tokens()),
            'versions' => LegalPage::query()
                ->where('type', $type)
                ->where('locale', $locale)
                ->whereNotNull('published_at')
                ->orderByDesc('version')
                ->get(),
        ]);
    }

    /** Save (or create) the draft without publishing it. */
    public function update(LegalPageRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $draft = LegalPage::draftFor($data['type'], $data['locale']);

        if ($draft) {
            $draft->update([
                'title' => $data['title'],
                'body' => $data['body'],
                'effective_date' => $data['effective_date'] ?? null,
            ]);
        } else {
            LegalPage::create([
                'type' => $data['type'],
                'locale' => $data['locale'],
                'version' => LegalPage::nextVersion($data['type'], $data['locale']),
                'title' => $data['title'],
                'body' => $data['body'],
                'effective_date' => $data['effective_date'] ?? null,
                'published_at' => null,
                'created_by' => $request->user()?->id,
            ]);
        }

        return redirect()
            ->route('admin.legal-pages.edit', [$data['type'], $data['locale']])
            ->with('success', __('admin.legal_pages_draft_saved'));
    }

    /** Freeze the draft as the next published version. */
    public function publish(LegalPageRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $draft = LegalPage::draftFor($data['type'], $data['locale']) ?? new LegalPage([
            'type' => $data['type'],
            'locale' => $data['locale'],
            'version' => LegalPage::nextVersion($data['type'], $data['locale']),
            'created_by' => $request->user()?->id,
        ]);

        $draft->fill([
            'title' => $data['title'],
            'body' => $data['body'],
            'effective_date' => $data['effective_date'] ?? now()->toDateString(),
            'published_at' => now(),
        ]);

        if (! $draft->exists) {
            $draft->version = LegalPage::nextVersion($data['type'], $data['locale']);
        }

        $draft->save();

        return redirect()
            ->route('admin.legal-pages.index')
            ->with('success', __('admin.legal_pages_published', ['version' => $draft->version]));
    }
}
