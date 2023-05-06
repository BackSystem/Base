<?php

namespace BackSystem\Base\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class SizeExtension extends AbstractExtension {

    public function getFilters(): array {
        return [
            new TwigFilter('format_size', [$this, 'formatSize']),
        ];
    }

    public function formatSize(int $bytes, int $decimals = 2): string {
        $size = ['o', 'ko', 'Mo', 'Go', 'To', 'Po', 'Eo', 'Zo', 'Yo'];
        $factor = floor((strlen((string) $bytes) - 1) / 3);

        return rtrim(rtrim(str_replace('.', ',', sprintf("%.{$decimals}f", $bytes / (1024 ** $factor))), '0'), ',') . ' ' . @$size[$factor];
    }

}
