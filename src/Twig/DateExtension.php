<?php

namespace BackSystem\Base\Twig;

use BackSystem\Base\Service\DateService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class DateExtension extends AbstractExtension
{
    public function __construct(private readonly DateService $dateService)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('format_datetime', [$this->dateService, 'formatDatetime']),
            new TwigFilter('format_date', [$this->dateService, 'formatDate']),
            new TwigFilter('format_time', [$this->dateService, 'formatTime']),
            new TwigFilter('age', [$this->dateService, 'age']),
        ];
    }
}
