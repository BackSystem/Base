<?php

namespace BackSystem\Base\Twig;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Extension\CoreExtension;
use Twig\TwigFilter;

final class SizeExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('format_size', $this->formatSize(...)),
        ];
    }

    public function __construct(private readonly Environment $environment)
    {
    }

    public function formatSize(int $bytes, int $decimals = 2, bool $binary = false): string
    {
        if ($binary) {
            $denominator = 1024;

            $size = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB', 'RB', 'QB'];
        } else {
            $denominator = 1000;

            $size = ['o', 'ko', 'Mo', 'Go', 'To', 'Po', 'Eo', 'Zo', 'Yo', 'Ro', 'Qo'];
        }

        $factor = floor((strlen((string) $bytes) - 1) / 3);

        return $this->environment->getExtension(CoreExtension::class)->formatNumber($bytes / ($denominator ** $factor), $decimals, null, '__').' '.@$size[$factor];
    }
}
