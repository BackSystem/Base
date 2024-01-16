<?php

namespace BackSystem\Base\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DateService
{
    private const DATE_FORMATS = [
        'none' => \IntlDateFormatter::NONE,
        'short' => \IntlDateFormatter::SHORT,
        'medium' => \IntlDateFormatter::MEDIUM,
        'long' => \IntlDateFormatter::LONG,
        'full' => \IntlDateFormatter::FULL,
    ];

    public function __construct(private readonly RequestStack $requestStack, private readonly TokenStorageInterface $tokenStorage, private readonly TranslatorInterface $translator)
    {
    }

    public function formatTime(\DateTimeInterface|string|null $dateTime, string $timeFormat = null, string $pattern = null): ?string
    {
        return $this->formatDatetime($dateTime, 'none', $timeFormat, $pattern);
    }

    public function formatDate(\DateTimeInterface|string|null $dateTime, string $dateFormat = null, string $pattern = null): ?string
    {
        return $this->formatDatetime($dateTime, $dateFormat, 'none', $pattern);
    }

    public function formatDatetime(\DateTimeInterface|string|null $dateTime, string $dateFormat = null, string $timeFormat = null, string $pattern = null): ?string
    {
        if (!$dateTime) {
            return null;
        }

        if (is_string($dateTime)) {
            try {
                $dateTime = new \DateTime($dateTime);
            } catch (\Exception $e) {
                throw new \RuntimeException($e);
            }
        }

        $locale = $this->getRequestStack()->getCurrentRequest()?->getLocale();

        $user = $this->getTokenStorage()->getToken()?->getUser();

        $timezone = 'Europe/Paris';

        if ($user) {
            $timezones = \DateTimeZone::listIdentifiers();

            if (method_exists($user, 'getTimezone') && in_array($user->getTimezone(), $timezones, true)) {
                $timezone = $user->getTimezone();
            }
        }

        $formatter = new \IntlDateFormatter($locale, self::DATE_FORMATS[$dateFormat] ?? self::DATE_FORMATS['full'], self::DATE_FORMATS[$timeFormat] ?? self::DATE_FORMATS['medium'], $timezone, null, $pattern);

        $format = $formatter->format($dateTime);

        return is_string($format) ? $format : null;
    }

    public function age(\DateTimeInterface $date, bool $onlyYear = false): string
    {
        $difference = (new \DateTimeImmutable('today'))->diff($date);
        $years = $difference->y;
        $months = $difference->m;
        $days = $difference->d;

        if ($onlyYear || (0 !== $years && 0 === $months && 0 === $days)) {
            return $this->getTranslator()->trans('{years} years old', [
                'years' => $years,
            ]);
        }

        if (0 !== $years && 0 !== $months && 0 === $days) {
            return $this->getTranslator()->trans('{years} years and {months} months old', [
                'years' => $years,
                'months' => $months,
            ]);
        }

        if (0 !== $years && 0 === $months && 0 !== $days) {
            return $this->getTranslator()->trans('{years} years and {days} days old', [
                'years' => $years,
                'days' => $days,
            ]);
        }

        return $this->getTranslator()->trans('{years} years, {months} months and {days} days old', [
            'years' => $years,
            'months' => $months,
            'days' => $days,
        ]);
    }

    private function getRequestStack(): RequestStack
    {
        return $this->requestStack;
    }

    private function getTokenStorage(): TokenStorageInterface
    {
        return $this->tokenStorage;
    }

    private function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }
}
