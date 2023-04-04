<?php

namespace BackSystem\Base\Helper;

class StringFormat
{
    public static function setTitle(string $string): string
    {
        $string = self::setSplit($string, '-');
        $string = self::setSplit($string, ' ');

        return self::setSplit($string, '.');
    }

    private static function setSplit(string $string, string $character): string
    {
        $new = '';

        if ('' === $character) {
            return $string;
        }

        $split = explode($character, $string);

        foreach ($split as $index => $item) {
            if (0 !== $index) {
                $new .= $character;
            }

            $new .= mb_convert_case($item, MB_CASE_TITLE, 'utf-8');
        }

        return $new;
    }
}
