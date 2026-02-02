<?php

namespace BackSystem\Base\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LocaleController
{
    public function __construct(private readonly Security $security, private readonly UrlGeneratorInterface $urlGenerator, private readonly string $redirectRoute)
    {
    }

    public function locale(string $locale, Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $request->getSession()->set('_locale', $locale);

        $user = $this->security->getUser();

        if ($user && method_exists($user, 'setLocale')) {
            $user->setLocale($locale);

            $entityManager->flush();
        }

        $referer = $request->headers->get('referer');

        if ($referer) {
            $path  = parse_url($referer, PHP_URL_PATH) ?? '';
            $query = parse_url($referer, PHP_URL_QUERY);

            $target = $path . ($query ? '?' . $query : '');

            if ($target !== '' && str_starts_with($target, '/') && !str_starts_with($target, '//')) {
                return new RedirectResponse($target, 302);
            }
        }

        return new RedirectResponse($this->urlGenerator->generate($this->redirectRoute), 302);
    }

}
