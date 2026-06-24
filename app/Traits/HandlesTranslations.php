<?php

// app/Traits/HandlesTranslations.php

namespace App\Traits;

trait HandlesTranslations
{
    public function setTranslation(string $key, string $locale, $value): self
    {
        $this->translations[$key][$locale] = $value;
        $this->save();

        return $this;
    }

    public function getTranslation(string $key, ?string $locale = null, bool $fallbackToDefault = true): ?string
    {
        $locale = $locale ?? app()->getLocale();

        if (isset($this->translations[$key][$locale])) {
            return $this->translations[$key][$locale];
        }

        if ($fallbackToDefault) {
            return $this->translations[$key][config('app.fallback_locale')] ?? null;
        }

        return null;
    }
}
