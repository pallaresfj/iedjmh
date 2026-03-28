<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class HtmlSanitizer
{
    /**
     * @var array<int, string>
     */
    private const ALLOWED_TAGS = [
        'p',
        'br',
        'strong',
        'em',
        'b',
        'i',
        'u',
        's',
        'ul',
        'ol',
        'li',
        'a',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'blockquote',
        'hr',
        'table',
        'thead',
        'tbody',
        'tfoot',
        'tr',
        'th',
        'td',
        'figure',
        'figcaption',
        'img',
    ];

    /**
     * @var array<string, array<int, string>>
     */
    private const ALLOWED_ATTRIBUTES = [
        'a' => ['href', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height'],
        'th' => ['colspan', 'rowspan'],
        'td' => ['colspan', 'rowspan'],
    ];

    public static function sanitize(?string $html): ?string
    {
        if (! filled($html)) {
            return $html;
        }

        $document = new DOMDocument('1.0', 'UTF-8');

        $wrappedHtml = '<div id="sanitized-root">'.$html.'</div>';

        $internalErrors = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="utf-8" ?>'.$wrappedHtml,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);

        $root = null;

        foreach ($document->getElementsByTagName('div') as $candidate) {
            if ($candidate->getAttribute('id') === 'sanitized-root') {
                $root = $candidate;

                break;
            }
        }

        if (! $root) {
            return strip_tags($html, '<p><br><strong><em><b><i><u><s><ul><ol><li><a><h1><h2><h3><h4><h5><h6><blockquote><hr><table><thead><tbody><tfoot><tr><th><td><figure><figcaption><img>');
        }

        static::sanitizeNodeChildren($root);

        $output = '';

        foreach ($root->childNodes as $child) {
            $output .= $document->saveHTML($child) ?: '';
        }

        return trim($output);
    }

    private static function sanitizeNodeChildren(DOMNode $node): void
    {
        $child = $node->firstChild;

        while ($child !== null) {
            $next = $child->nextSibling;
            static::sanitizeNode($child);
            $child = $next;
        }
    }

    private static function sanitizeNode(DOMNode $node): void
    {
        if ($node->nodeType === XML_ELEMENT_NODE) {
            /** @var DOMElement $element */
            $element = $node;
            $tag = strtolower($element->tagName);

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed'], true)) {
                    static::removeNode($element);

                    return;
                }

                static::unwrapNode($element);

                return;
            }

            static::sanitizeAttributes($element, $tag);
        }

        static::sanitizeNodeChildren($node);
    }

    private static function sanitizeAttributes(DOMElement $element, string $tag): void
    {
        $allowedAttributes = self::ALLOWED_ATTRIBUTES[$tag] ?? [];

        if ($element->hasAttributes()) {
            for ($index = $element->attributes->length - 1; $index >= 0; $index--) {
                $attribute = $element->attributes->item($index);

                if (! $attribute) {
                    continue;
                }

                $name = strtolower($attribute->name);

                if (! in_array($name, $allowedAttributes, true)) {
                    $element->removeAttributeNode($attribute);
                }
            }
        }

        if (in_array($tag, ['th', 'td'], true)) {
            static::sanitizePositiveIntegerAttribute($element, 'colspan');
            static::sanitizePositiveIntegerAttribute($element, 'rowspan');
        }

        if ($tag === 'img') {
            $src = trim((string) $element->getAttribute('src'));

            if (! static::isSafeUrl($src)) {
                static::removeNode($element);

                return;
            }

            static::sanitizePositiveIntegerAttribute($element, 'width');
            static::sanitizePositiveIntegerAttribute($element, 'height');

            return;
        }

        if ($tag !== 'a') {
            return;
        }

        $href = trim((string) $element->getAttribute('href'));

        if (! static::isSafeUrl($href)) {
            $element->removeAttribute('href');
            $element->removeAttribute('target');
            $element->removeAttribute('rel');

            return;
        }

        $target = strtolower(trim((string) $element->getAttribute('target')));

        if ($target !== '_blank') {
            $element->removeAttribute('target');
            $element->removeAttribute('rel');

            return;
        }

        $element->setAttribute('rel', 'noopener noreferrer');
    }

    private static function sanitizePositiveIntegerAttribute(DOMElement $element, string $attribute): void
    {
        if (! $element->hasAttribute($attribute)) {
            return;
        }

        $value = trim((string) $element->getAttribute($attribute));

        if ($value === '' || preg_match('/^[1-9][0-9]*$/', $value) !== 1) {
            $element->removeAttribute($attribute);
        }
    }

    private static function unwrapNode(DOMElement $element): void
    {
        $parent = $element->parentNode;

        if (! $parent) {
            return;
        }

        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }

    private static function removeNode(DOMElement $element): void
    {
        $parent = $element->parentNode;

        if (! $parent) {
            return;
        }

        $parent->removeChild($element);
    }

    private static function isSafeUrl(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        $decodedUrl = html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (preg_match('/^\s*(javascript|vbscript|data):/i', $decodedUrl) === 1) {
            return false;
        }

        if (preg_match('/^(https?:|mailto:|tel:|\/|#)/i', $decodedUrl) === 1) {
            return true;
        }

        return preg_match('/^[a-z][a-z0-9+\-.]*:/i', $decodedUrl) !== 1;
    }
}
