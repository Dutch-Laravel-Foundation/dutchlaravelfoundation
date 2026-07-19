<?php

declare(strict_types=1);

namespace App\Modifiers;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Statamic\Modifiers\Modifier;

final class ProgressiveMedia extends Modifier
{
    public function index(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="UTF-8">'.$value,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($document);
        $images = $xpath->query('//img');
        $enhanced = false;

        if ($images === false) {
            return $value;
        }

        foreach (iterator_to_array($images) as $image) {
            if (! $image instanceof DOMElement || $image->hasAttribute('data-progressive-media')) {
                continue;
            }

            $dimensions = $this->dimensions($image);

            if ($dimensions === null) {
                continue;
            }

            [$width, $height] = $dimensions;
            $image->setAttribute('width', (string) $width);
            $image->setAttribute('height', (string) $height);
            $image->setAttribute('loading', 'lazy');
            $image->setAttribute('decoding', 'async');
            $image->setAttribute('data-progressive-media', '');
            $image->setAttribute('data-media-state', 'loading');
            $image->setAttribute('onload', "try{var entries=performance.getEntriesByName(this.currentSrc);var entry=entries[entries.length-1];if(entry&&new window.URL(this.currentSrc,location.href).origin===location.origin&&entry.transferSize===0&&entry.decodedBodySize>0){this.dataset.mediaCached=''}}catch(error){}this.dataset.mediaState='loaded'");
            $this->wrap($document, $image);
            $enhanced = true;
        }

        if (! $enhanced) {
            return $value;
        }

        $html = $document->saveHTML();

        return preg_replace('/^<\?xml encoding="UTF-8"\?>/', '', $html) ?? $value;
    }

    /** @return array{int, int}|null */
    private function dimensions(DOMElement $image): ?array
    {
        $width = filter_var($image->getAttribute('width'), FILTER_VALIDATE_INT);
        $height = filter_var($image->getAttribute('height'), FILTER_VALIDATE_INT);

        if ($width !== false && $height !== false && $width > 0 && $height > 0) {
            return [$width, $height];
        }

        $source = $image->getAttribute('src');

        if (preg_match('/#media-([1-9][0-9]*)x([1-9][0-9]*)$/', $source, $matches) === 1) {
            $image->setAttribute('src', substr($source, 0, -strlen($matches[0])));

            return [(int) $matches[1], (int) $matches[2]];
        }

        $path = parse_url($source, PHP_URL_PATH);

        if (! is_string($path) || ! preg_match('/\.(?:avif|gif|jpe?g|png|webp)$/i', $path)) {
            return null;
        }

        $publicPath = public_path(ltrim(rawurldecode($path), '/'));

        if (! is_file($publicPath)) {
            return null;
        }

        $size = getimagesize($publicPath);

        if ($size === false) {
            return null;
        }

        return [$size[0], $size[1]];
    }

    private function wrap(DOMDocument $document, DOMElement $image): void
    {
        $parent = $image->parentNode;

        if ($parent instanceof DOMElement && $parent->hasAttribute('data-progressive-media-frame')) {
            return;
        }

        $frame = $document->createElement('span');
        $frame->setAttribute('class', 'dlf-inline-progressive-media');
        $frame->setAttribute('data-progressive-media-frame', '');
        $parent?->replaceChild($frame, $image);
        $frame->appendChild($image);
    }
}
