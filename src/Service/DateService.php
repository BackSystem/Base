<?php

namespace BackSystem\Base\Service;

use BackSystem\Base\Helper\DateHelper;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Extra\Intl\IntlExtension;

class DateService
{
    public function __construct(private readonly IntlExtension $intlExtension, private readonly Environment $environment, private readonly RequestStack $requestStack, private readonly TokenStorageInterface $tokenStorage, private readonly TranslatorInterface $translator)
    {
    }

    public function getFromRequest(): \DateTimeImmutable
    {
        $date = DateHelper::getDateTimeImmutable($this->getRequestStack()->getCurrentRequest()?->query->getString('date'));

        if (!$date) {
            $date = new \DateTimeImmutable('today');
        }

        return $date;
    }

    private function getUserTimezone(): string
    {
        $timezone = 'Europe/Paris';

        $user = $this->getTokenStorage()->getToken()?->getUser();

        if ($user) {
            $timezones = \DateTimeZone::listIdentifiers();

            if (method_exists($user, 'getTimezone') && in_array($user->getTimezone(), $timezones, true)) {
                $timezone = $user->getTimezone();
            }
        }

        return $timezone;
    }

    /**
     * @param \DateTimeInterface|string|null  $date     A date or null to use the current time
     * @param \DateTimeZone|string|false|null $timezone The target timezone, null to use the default, false to leave unchanged
     */
    public function formatDateTime($date, ?string $dateFormat = 'medium', ?string $timeFormat = 'medium', string $pattern = '', $timezone = null, string $calendar = 'gregorian', ?string $locale = null, bool $localized = false): string
    {
        if ($localized) {
            $timezone = $this->getUserTimezone();
            $locale = $this->getRequestStack()->getCurrentRequest()?->getLocale();
        }

        return $this->getIntlExtension()->formatDateTime($this->getEnvironment(), $date, $dateFormat, $timeFormat, $pattern, $timezone, $calendar, $locale);
    }

    /**
     * @param \DateTimeInterface|string|null  $date     A date or null to use the current time
     * @param \DateTimeZone|string|false|null $timezone The target timezone, null to use the default, false to leave unchanged
     */
    public function formatDate($date, ?string $dateFormat = 'medium', string $pattern = '', $timezone = null, string $calendar = 'gregorian', ?string $locale = null, bool $localized = false): string
    {
        if ($localized) {
            $timezone = $this->getUserTimezone();
            $locale = $this->getRequestStack()->getCurrentRequest()?->getLocale();
        }

        return $this->getIntlExtension()->formatDate($this->getEnvironment(), $date, $dateFormat, $pattern, $timezone, $calendar, $locale);
    }

    /**
     * @param \DateTimeInterface|string|null  $date     A date or null to use the current time
     * @param \DateTimeZone|string|false|null $timezone The target timezone, null to use the default, false to leave unchanged
     */
    public function formatTime($date, ?string $timeFormat = 'medium', string $pattern = '', $timezone = null, string $calendar = 'gregorian', ?string $locale = null, bool $localized = false): string
    {
        if ($localized) {
            $timezone = $this->getUserTimezone();
            $locale = $this->getRequestStack()->getCurrentRequest()?->getLocale();
        }

        return $this->getIntlExtension()->formatTime($this->getEnvironment(), $date, $timeFormat, $pattern, $timezone, $calendar, $locale);
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

    private function getIntlExtension(): IntlExtension
    {
        return $this->intlExtension;
    }

    private function getEnvironment(): Environment
    {
        return $this->environment;
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
