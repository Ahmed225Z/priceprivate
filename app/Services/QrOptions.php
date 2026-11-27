<?php

declare(strict_types=1);

namespace App\Services;

final readonly class QrOptions
{
    public function __construct(
        public string $format = 'png',
        public int $size = 1024,
        public bool $withLogo = false,
        public string $foreground = '#000000',
        public string $background = '#FFFFFF',
    ) {}

    /** Same options always give the same file name. */
    public function cacheKey(): string
    {
        return substr(hash('sha256', json_encode([
            $this->size, $this->withLogo, strtolower($this->foreground), strtolower($this->background),
        ])), 0, 10);
    }
}
