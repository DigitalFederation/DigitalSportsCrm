<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Request;

class MenuHelper
{
    /**
     * If the request URI matches any of the constructed patterns, then the "active" variable is set to true, indicating that the specified menu is active.
     * Finally, the function returns the boolean value of "active".
     *
     * @param  string  $uri
     * @return bool
     */
    public static function isActiveMenu($uri = '')
    {
        $active = false;
        if (Request::is(Request::segment(1).'/'.$uri.'/*') || Request::is(Request::segment(1).'/'.$uri) || Request::is($uri)) {
            $active = true;
        }

        return $active;
    }

    /**
     * Safely convert any value to a string for translation
     * and apply translation if needed
     *
     * @param  mixed  $value
     * @param  bool  $translate  Whether to apply translation function
     * @return string
     */
    public static function safeTranslationString($value, $translate = true)
    {
        // First convert to string safely
        $stringValue = '';

        if (is_array($value)) {
            $stringValue = json_encode($value);
        } elseif (is_object($value) && ! method_exists($value, '__toString')) {
            $stringValue = json_encode($value);
        } elseif ($value === null) {
            $stringValue = '';
        } else {
            $stringValue = (string) $value;
        }

        // Then apply translation if requested
        if ($translate && ! empty($stringValue)) {
            return __($stringValue);
        }

        return $stringValue;
    }
}
