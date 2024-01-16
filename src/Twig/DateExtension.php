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
            new TwigFilter('datetime_format', [$this->getDateService(), 'formatDatetime']),
            new TwigFilter('date_format', [$this->getDateService(), 'formatDate']),
            new TwigFilter('time_format', [$this->getDateService(), 'formatTime']),
            new TwigFilter('age', [$this->getDateService(), 'age']),
        ];
    }

    private function getDateService(): DateService
    {
        return $this->dateService;
    }
}
