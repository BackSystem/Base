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

    public function ago(\DateTimeInterface $input): string
    {
        return $this->duration((new \DateTime())->diff($input));
    }

    public function duration(int|string|\DateTimeInterface|\DateInterval $input, bool $onlyDate = false): string
    {
        $dtF = new \DateTime('@0');

        if (!$input instanceof \DateInterval) {
            if ($input instanceof \DateTimeInterface) {
                $dtT = $input;
            } else {
                $dtT = new \DateTime("@$input");
            }

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
            $time = implode(', ', $parts).' '.$this->translator->trans('and').' '.$last;

            return $this->translator->trans('%time% ago', ['%time%' => $time]);
        }

        return $this->translator->trans('%time% ago', ['%time%' => $last ?? '']);
    }
}
