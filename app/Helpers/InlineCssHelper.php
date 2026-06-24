<?php

use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

if (! function_exists('inlineCss')) {
    function inlineCss($html, $css)
    {
        $cssToInlineStyles = new CssToInlineStyles;

        return $cssToInlineStyles->convert(
            $html,
            $css
        );
    }
}
