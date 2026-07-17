<?php

namespace App\Support;

/**
 * Convert rich-editor HTML into readable plain text.
 *
 * The editor stores merge tags as empty `<span data-type="mergeTag" ...>`
 * elements (the visible `{{ id }}` text is rendered client-side), so they are
 * substituted back to their placeholder form before stripping tags. Block
 * boundaries are turned into spaces so adjacent paragraphs don't collapse
 * into one word, and HTML entities are decoded so apostrophes and other
 * escaped characters render naturally.
 */
class HtmlToText
{
    public static function convert(?string $html): string
    {
        if (blank($html)) {
            return '';
        }

        $html = preg_replace_callback(
            '/<span\b[^>]*\bdata-type=(["\'])mergeTag\1[^>]*\bdata-id=(["\'])([^"\']+)\2[^>]*>\s*<\/span>/i',
            fn (array $matches): string => '{{ '.$matches[3].' }}',
            $html,
        ) ?? $html;

        $html = preg_replace('/<\s*br\s*\/?>|<\/(p|div|li|h[1-6]|tr|blockquote)\s*>/i', ' ', $html) ?? $html;

        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }
}
