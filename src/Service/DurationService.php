<?php

namespace BackSystem\Base\Service;

use Symfony\Contracts\Translation\TranslatorInterface;

final class DurationService
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function convertToHoursMinutes(float $decimalHours): string
    {
        $hours = floor($decimalHours);
        $minutes = round(($decimalHours - $hours) * 60);

        return sprintf('%dh%02d', $hours, $minutes);
    }

    public function ago(\DateTimeInterface $input, bool $onlyDate = false, ?int $numberOfParts = null): string
    {
        $difference = (new \DateTime())->diff($input);

        $duration = $this->duration($difference, $onlyDate, $numberOfParts);

        if ($difference->invert) {
            return $this->translator->trans('%time% ago', ['%time%' => $duration]);
        }

        return $this->translator->trans('in %time%', ['%time%' => $duration]);
    }

    public function duration(int|\DateInterval $input, bool $onlyDate = false, ?int $numberOfParts = null): string
    {
        $dateTimeFrom = new \DateTime('@0');

        if ($input instanceof \DateInterval) {
            $difference = $input;
        } else {
            $dateTimeTo = (new \DateTime())->setTimestamp((int) $input);

            $difference = $dateTimeFrom->diff($dateTimeTo);
        }

        $parts = [];

        $years = $difference->y;
        $months = $difference->m;
        $days = $difference->d;
        $hours = $difference->h;
        $minutes = $difference->i;
        $seconds = $difference->s;

        if ($years > 0) {
            $parts[] = $years.' '.$this->translator->trans($years > 1 ? 'years' : 'year');
        }

        if ($months > 0) {
            $parts[] = $months.' '.$this->translator->trans($months > 1 ? 'months' : 'month');
        }

        if ($days > 0) {
            $parts[] = $days.' '.$this->translator->trans($days > 1 ? 'days' : 'day');
        }

        if (false === $onlyDate) {
            if ($hours > 0) {
                $parts[] = $hours.' '.$this->translator->trans($hours > 1 ? 'hours' : 'hour');
            }

            if ($minutes > 0) {
                $parts[] = $minutes.' '.$this->translator->trans($minutes > 1 ? 'minutes' : 'minute');
            }

            if ($seconds > 0) {
                $parts[] = $seconds.' '.$this->translator->trans($seconds > 1 ? 'seconds' : 'second');
            }
        }

        if ($numberOfParts) {
            $parts = array_slice($parts, 0, $numberOfParts);
        }

        $last = array_pop($parts);

        if ($parts) {
            return implode(', ', $parts).' '.$this->translator->trans('and').' '.$last;
        }

        return $last ?? '';
    }

    public function shortDuration(int|float|\DateInterval $input, string $unit = 'time'): string
    {
        $dateTimeFrom = new \DateTime('@0');

        if ($input instanceof \DateInterval) {
            $difference = $input;
        } else {
            $dateTimeTo = (new \DateTime())->setTimestamp((int) $input);

            $difference = $dateTimeFrom->diff($dateTimeTo);
        }

        $hours = $difference->d * 24 + $difference->h;
        $minutes = $difference->i;

        if ('day' === $unit) {
            $days = $hours / 24;

            $number = number_format($days, 2, ',');

            while (str_ends_with($number, '0')) {
                $number = substr($number, 0, -1);
            }

            if (str_ends_with($number, ',')) {
                $number = substr($number, 0, -1);
            }

            return $number.'j';
        }

        if ($hours > 0 && $minutes > 0) {
            return $hours.'h'.str_pad((string) $minutes, 2, '0', STR_PAD_LEFT);
        }

        if ($hours > 0) {
            return $hours.'h';
        }

        if ($minutes > 0) {
            return $minutes.' min';
        }

        return '';
    }
}
