<?php

namespace BackSystem\Base\Twig;

use BackSystem\Base\Service\DurationService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class DurationExtension extends AbstractExtension
{
    public function __construct(private readonly DurationService $durationService)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('ago', [$this->durationService, 'ago']),
            new TwigFilter('duration', [$this->durationService, 'duration']),
            new TwigFilter('short_duration', [$this->durationService, 'shortDuration']),
            new TwigFilter('hours_minutes', [$this->durationService, 'convertToHoursMinutes']),
        ];
    }
}
