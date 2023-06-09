<?php

namespace BackSystem\Base\Twig;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TimeExtension extends AbstractExtension
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('ago', [$this, 'ago']),
            new TwigFilter('duration', [$this, 'duration']),
        ];
    }

    public function ago(\DateTimeInterface $input, bool $onlyDate = false): string
    {
        $difference = (new \DateTime())->diff($input);

        $duration = $this->duration($difference, $onlyDate);

        if ($difference->invert) {
            return $this->translator->trans('%time% ago', ['%time%' => $duration]);
        }

        return $this->translator->trans('in %time%', ['%time%' => $duration]);
    }

    public function duration(int|\DateInterval $input, bool $onlyDate = false): string
    {
        $dtF = new \DateTime('@0');

        if (!$input instanceof \DateInterval) {
            $dtT = new \DateTime("@$input");

            $difference = $dtF->diff($dtT);
        } else {
            $difference = $input;
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

        $last = array_pop($parts);

        if ($parts) {
            return implode(', ', $parts).' '.$this->translator->trans('and').' '.$last;
        }

        return $last ?? '';
    }
}
