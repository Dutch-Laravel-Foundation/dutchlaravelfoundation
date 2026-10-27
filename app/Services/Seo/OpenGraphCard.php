<?php

declare(strict_types=1);

namespace App\Services\Seo;

final readonly class OpenGraphCard
{
    public function __construct(
        public string $eyebrow,
        public string $title,
        public string $photoPath,
        public float $photoFocusX = 0.5,
    ) {}

    /**
     * Short fingerprint of everything that changes the rendered image, used as cache key and cache buster.
     */
    public function fingerprint(): string
    {
        return substr(hash('sha256', implode("\0", [
            OpenGraphImage::VERSION,
            $this->eyebrow,
            $this->title,
            $this->photoPath,
            (string) @filesize($this->photoPath),
            (string) @filemtime($this->photoPath),
            (string) $this->photoFocusX,
        ])), 0, 16);
    }
}
