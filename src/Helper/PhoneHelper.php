<?php

namespace BackSystem\Base\Helper;

class PhoneHelper
{
    public static function format(?string $phone): ?string
    {
        $originalPhone = $phone;

        if (!$phone) {
            return $originalPhone;
        }

        $phone = preg_replace('/[^0-9+]/', '', $phone);

        if (!$phone) {
            return $originalPhone;
        }

        if (10 === strlen($phone)) {
            return trim(strrev(chunk_split(strrev($phone), 2, ' ')));
        }

        if (str_starts_with($phone, '+')) {
            if (12 === strlen($phone)) {
                $number = substr($phone, 3);

                return substr($phone, 0, 3).' '.strrev(chunk_split(strrev($number), 2, ' '));
            }

            if (13 === strlen($phone)) {
                $number = substr($phone, 3);

                return strrev(chunk_split(strrev($number), 2, ' '));
            }
        }

        return $originalPhone;
    }
}
