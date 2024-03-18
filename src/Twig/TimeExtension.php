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
            new TwigFilter('short_duration', [$this, 'shortDuration']),
        ];
    }

    public function ago(\DateTimeInterface $input, bool $onlyDate = false): string
    {
        $difference = (new \DateTime())->diff($input);

        $duration = $this->duration($difference, $onlyDate);

        if ($difference->invert) {
            return $this->getTranslator()->trans('%time% ago', ['%time%' => $duration]);
        }

        return $this->getTranslator()->trans('in %time%', ['%time%' => $duration]);
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
            $parts[] = $years.' '.$this->getTranslator()->trans($years > 1 ? 'years' : 'year');
        }

        if ($months > 0) {
            $parts[] = $months.' '.$this->getTranslator()->trans($months > 1 ? 'months' : 'month');
        }

        if ($days > 0) {
            $parts[] = $days.' '.$this->getTranslator()->trans($days > 1 ? 'days' : 'day');
        }

        if (false === $onlyDate) {
            if ($hours > 0) {
                $parts[] = $hours.' '.$this->getTranslator()->trans($hours > 1 ? 'hours' : 'hour');
            }

            if ($minutes > 0) {
                $parts[] = $minutes.' '.$this->getTranslator()->trans($minutes > 1 ? 'minutes' : 'minute');
            }

            if ($seconds > 0) {
                $parts[] = $seconds.' '.$this->getTranslator()->trans($seconds > 1 ? 'seconds' : 'second');
            }
        }

        $last = array_pop($parts);

        if ($parts) {
            return implode(', ', $parts).' '.$this->getTranslator()->trans('and').' '.$last;
        }

        return $last ?? '';
    }

    public function shortDuration(int|\DateInterval $input): string
    {
        $dtF = new \DateTime('@0');

        if (!$input instanceof \DateInterval) {
            $dtT = new \DateTime("@$input");

            $difference = $dtF->diff($dtT);
        } else {
            $difference = $input;
        }

        $hours = $difference->d * 24 + $difference->h;
        $minutes = $difference->i;

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

    private function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }
}
