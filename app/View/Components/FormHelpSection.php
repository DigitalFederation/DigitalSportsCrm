<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\View\Component;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Exception\MissingDependencyException;

class FormHelpSection extends Component
{
    public string $content = '';
    public string $key;

    /**
     * Create a new component instance.
     *
     * @param  string  $key  A unique key to identify the help content (used for file lookup and element IDs).
     */
    public function __construct(string $key)
    {
        $this->key = $key;
        $this->content = $this->loadAndParseMarkdown($key);
    }

    /**
     * Load and parse the Markdown content based on current locale with fallback.
     */
    protected function loadAndParseMarkdown(string $key): string
    {
        $currentLocale = App::getLocale();
        $fallbackLocale = config('app.fallback_locale', 'en'); // Ensure fallback locale is set in config/app.php

        $localePath = resource_path("markdown/help/{$currentLocale}/{$key}.md");
        $path = null;

        if (File::exists($localePath)) {
            $path = $localePath;
        } elseif ($currentLocale !== $fallbackLocale) {
            $fallbackPath = resource_path("markdown/help/{$fallbackLocale}/{$key}.md");
            if (File::exists($fallbackPath)) {
                $path = $fallbackPath;
                Log::debug("Help markdown for key '{$key}' not found for locale '{$currentLocale}', using fallback '{$fallbackLocale}'."); // Optional: Debugging
            }
        }

        if ($path === null) {
            Log::warning("Help markdown file not found for key '{$key}' in locale '{$currentLocale}' or fallback '{$fallbackLocale}'.");

            // Use Laravel's translation for the error message itself
            return '<p class="text-sm text-red-600">' . __('Help content not available.') . '</p>';
        }

        $markdown = File::get($path);

        try {
            // Basic configuration - you can add extensions (like tables, attributes) if needed
            $converter = new CommonMarkConverter;

            return $converter->convert($markdown)->getContent();
        } catch (MissingDependencyException $e) {
            Log::error("CommonMark missing dependency for help key '{$key}': " . $e->getMessage());

            return '<p class="text-sm text-red-600">' . __('Error rendering help content.') . '</p>';
        } catch (\Exception $e) {
            Log::error("Error parsing help markdown file {$path}: " . $e->getMessage());

            return '<p class="text-sm text-red-600">' . __('Error rendering help content.') . '</p>';
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        // The 'content' variable is automatically available in the component's view
        return view('components.form-help-section');
    }
}
