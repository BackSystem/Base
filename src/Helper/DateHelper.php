<?php

namespace BackSystem\Base\Helper;

class DateHelper
{
    /**
     * Sets the correct date (Y-m-d) to $value based on current $date, $startTime and $endTime.
     */
    public static function setCorrectDate(\DateTimeInterface $date, \DateTimeInterface $startTime, \DateTimeInterface $endTime, \DateTimeInterface $value): \DateTimeInterface
    {
        $newEndTime = \DateTime::createFromInterface($endTime);

        if ($startTime > $endTime) {
            $newEndTime = $newEndTime->modify('+1 day');
        }

        $difference = $newEndTime->getTimestamp() - $startTime->getTimestamp();

        $reference = (new \DateTimeImmutable())->setTimestamp($startTime->getTimestamp() + ($difference / 2))->modify('+12 hours')->setDate(1970, 01, 01);

        $value = $value->setDate(1970, 01, 01);

        $value = $value->setDate((int) $date->format('Y'), (int) $date->format('m'), (int) $date->format('d'));

        if ($startTime->format('d') !== $endTime->format('d')) {
            if ((int) $value->format('Hi') >= 0 && (int) $value->format('Hi') < (int) $reference->format('Hi')) {
                $value = $value->modify('+1 day');
            }
        }

        return $value;
    }

    public static function getDateTime(\DateTimeInterface|string|null $value, string $format = 'Y-m-d'): ?\DateTime
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof \DateTime) {
            return $value;
        }

        if ($value instanceof \DateTimeImmutable) {
            $date = \DateTime::createFromImmutable($value);
        } else {
            $date = \DateTime::createFromFormat($format, $value);
        }

        return $date && $date->format($format) === $value ? $date->setTime(0, 0) : null;
    }

    public static function getDateTimeImmutable(\DateTimeInterface|string|null $value, string $format = 'Y-m-d'): ?\DateTimeImmutable
    {
        if ($value instanceof \DateTimeImmutable) {
            $value = \DateTime::createFromImmutable($value);
        }

        $value = self::getDateTime($value, $format);

        return $value ? \DateTimeImmutable::createFromMutable($value) : null;
    }

    public static function getDateImmutable(?string $value, string $default = 'today'): \DateTimeImmutable
    {
        $value = self::getDateTime($value, 'Y-m-d');

        return $value ? \DateTimeImmutable::createFromMutable($value) : new \DateTimeImmutable($default);
    }

    /**
     * @return ($dateTime is \DateTime ? \DateTime : \DateTimeImmutable)
     */
    public static function setDate(\DateTime|\DateTimeImmutable $dateTime, \DateTimeInterface $date): \DateTime|\DateTimeImmutable
    {
        return $dateTime->setDate((int) $date->format('Y'), (int) $date->format('m'), (int) $date->format('d'));
    }

    public static function createFromDateAndTime(\DateTimeInterface $date, \DateTimeInterface $time): \DateTime
    {
        $dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $date->format('Y-m-d').' '.$time->format('H:i:s'));

        if (!$dateTime) {
            throw new \RuntimeException('Unable to create the DateTime from this date and this time.');
        }

        return $dateTime;
    }

    public static function getMiddleDateTime(\DateTimeInterface $firstDateTime, \DateTimeInterface $lastDateTime): \DateTime
    {
        $firstDateTime = \DateTime::createFromInterface($firstDateTime);

        return $firstDateTime->modify('+'.($lastDateTime->getTimestamp() - $firstDateTime->getTimestamp()) / 2 .' seconds');
    }
}
