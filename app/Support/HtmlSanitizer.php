<?php

namespace App\Support;

class HtmlSanitizer
{
    /** Strip dangerous elements and event handlers from admin-entered HTML. */
    public static function clean(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        // Remove script/iframe/object/embed blocks entirely
        $html = preg_replace('/<(script|iframe|object|embed|form)\b[^>]*>.*?<\/\1>/is', '', $html) ?? $html;
        // Remove self-closing variants (e.g. <script />)
        $html = preg_replace('/<(script|iframe|object|embed|form)\b[^>]*\/?\s*>/i', '', $html) ?? $html;
        // Remove on* event handler attributes
        $html = preg_replace('/\s+on\w+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]*)/i', '', $html) ?? $html;
        // Replace javascript: and data: in href/src/action
        $html = preg_replace('/\b(href|src|action)\s*=\s*["\']?\s*(javascript|vbscript|data):/i', '$1="#"', $html) ?? $html;

        return $html;
    }
}
