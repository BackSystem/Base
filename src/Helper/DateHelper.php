<?php

namespace BackSystem\Base\Helper;

class DateHelper
{
    public static function getDateTime(\DateTime|string|null $value, string $format = 'Y-m-d'): ?\DateTime
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof \DateTime) {
            return $value;
        }

        $date = \DateTime::createFromFormat($format, $value);

        return $date && $date->format($format) === $value ? $date->setTime(0, 0) : null;
    }

    public static function setDate(\DateTimeInterface $dateTime, \DateTimeInterface $date): \DateTime
    {
        if (!$dateTime instanceof \DateTime) {
            $dateTime = \DateTime::createFromInterface($dateTime);
        }

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
