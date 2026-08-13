<?php

namespace App\Services;

use App\Models\LegalPage;
use App\Models\SiteSetting;
use Illuminate\Support\Carbon;

/**
 * Resolves {{placeholder}} tokens in a stored legal page body.
 *
 * Tokens are resolved at render time rather than save time, so an install that
 * rebrands (or edits its address in Home page settings) sees every published legal
 * page update without re-editing them.
 *
 * Values are escaped before substitution: the body is already-sanitized HTML, and a
 * hostile value in a setting or env var must not be able to inject markup into it.
 */
class LegalPageRenderer
{
    public function render(LegalPage $page): string
    {
        return $this->renderBody($page->body, $page->effective_date);
    }

    public function renderBody(?string $body, Carbon|string|null $effectiveDate = null): string
    {
        $body = (string) $body;

        if ($body === '') {
            return '';
        }

        return strtr($body, $this->tokens($effectiveDate));
    }

    /**
     * The token map. Site settings win over branding config, so the Home page
     * settings screen doubles as the contact-details editor for legal pages.
     *
     * @return array<string, string>
     */
    public function tokens(Carbon|string|null $effectiveDate = null): array
    {
        $brand = config('branding.primary');
        $international = config('branding.international');

        $values = [
            '{{federation_name}}' => SiteSetting::get('federation_name', $brand['name']),
            '{{federation_short_name}}' => $brand['short_name'],
            '{{portal_name}}' => SiteSetting::get('app_name', $brand['portal_name']),
            '{{federation_address}}' => SiteSetting::get('federation_address', $brand['address']),
            '{{federation_email}}' => $brand['email'],
            '{{support_email}}' => SiteSetting::get('federation_support_email', $brand['support_email']),
            '{{federation_phone}}' => $brand['phone'],
            '{{portal_url}}' => $brand['website_url'],
            '{{international_federation_name}}' => $international['name'],
            '{{international_federation_short_name}}' => $international['short_name'],
            '{{effective_date}}' => $this->formatDate($effectiveDate),
        ];

        return array_map(fn ($value) => e((string) $value), $values);
    }

    /**
     * The reverse map, used when importing legacy content: swaps literal brand
     * values back into tokens so imported pages stay rebrand-aware.
     *
     * Longest values first, so a short name nested inside a long one does not
     * partially replace it.
     *
     * @return array<string, string>
     */
    public function detokenize(): array
    {
        $map = [];

        foreach ($this->tokens() as $token => $value) {
            if ($token === '{{effective_date}}') {
                continue;
            }

            $value = trim($value);

            // Very short values would match far too much prose.
            if (mb_strlen($value) < 4) {
                continue;
            }

            $map[$value] = $token;
        }

        uksort($map, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        return $map;
    }

    private function formatDate(Carbon|string|null $date): string
    {
        if ($date === null) {
            return '';
        }

        return $date instanceof Carbon
            ? $date->format('d/m/Y')
            : (string) $date;
    }
}
