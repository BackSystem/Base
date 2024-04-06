<?php

namespace BackSystem\Base\Twig;

use BackSystem\Base\Service\DateService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DateExtension extends AbstractExtension
{
    public function __construct(private readonly DateService $dateService)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('format_datetime', [$this->getDateService(), 'formatDatetime']),
            new TwigFilter('format_date', [$this->getDateService(), 'formatDate']),
            new TwigFilter('format_time', [$this->getDateService(), 'formatTime']),
            new TwigFilter('age', [$this->getDateService(), 'age']),
        ];
    }

    private function getDateService(): DateService
    {
        return $this->dateService;
    }
}
