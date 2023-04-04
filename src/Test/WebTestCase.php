<?php

namespace BackSystem\Base\Test;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class WebTestCase extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{
    private KernelBrowser $client;

    private EntityManagerInterface $entityManager;

    private TranslatorInterface $translator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();

        try {
            /** @var EntityManagerInterface $entityManager */
            $entityManager = self::getContainer()->get(EntityManagerInterface::class);

            /** @var TranslatorInterface $translator */
            $translator = self::getContainer()->get(TranslatorInterface::class);

            $this->entityManager = $entityManager;
            $this->translator = $translator;
        } catch (\Exception $exception) {
            throw new \RuntimeException($exception);
        }

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->entityManager->clear();

        parent::tearDown();
    }

    protected function getClient(): KernelBrowser
    {
        return $this->client;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    protected function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    protected function expectAlert(string $type, string $message = null): void
    {
        $alert = $this->client->getCrawler()->filter('.alert.alert-'.$type);

        $this->assertEquals(1, $alert->count());

        if ($message) {
            $message = $this->translator->trans($message);

            $this->assertStringContainsString($message, $alert->text());
        }
    }

    protected function expectInvalidField(string $name): void
    {
        $this->assertEquals(1, $this->client->getCrawler()->filter('[name="'.$name.'"].is-invalid')->count());
    }

    protected function expectValidField(string $name): void
    {
        $this->assertEquals(0, $this->client->getCrawler()->filter('[name="'.$name.'"].is-invalid')->count());
    }

    protected function expectInvalidFeedback(string $name, string $message): void
    {
        $text = $this->client->getCrawler()->filter('[name="'.$name.'"]')->ancestors()->filter('.invalid-feedback')->text();

        $this->assertEquals($message, $text);
    }

    protected function expectDisabledField(string $name): void
    {
        $this->assertEquals(1, $this->client->getCrawler()->filter('[name="'.$name.'"]:disabled')->count());
    }

    protected function expectOptionsCount(string $name, int $count): void
    {
        $this->assertEquals($count, $this->client->getCrawler()->filter('[name="'.$name.'"]')->children()->count());
    }

    protected function expectH1(string $title): void
    {
        $title = $this->translator->trans($title);
        $crawler = $this->client->getCrawler();

        $this->assertEquals($title, $crawler->filter('h1')->text(), '<h1> mismatch.');
    }

    protected function expectTitle(string $title): void
    {
        $title = $this->translator->trans($title);

        $crawler = $this->client->getCrawler();

        $this->assertEquals($title, $crawler->filter('title')->text(), '<title> mismatch.');
    }

    protected function login(?UserInterface $user): void
    {
        if (null === $user) {
            return;
        }

        $this->client->loginUser($user);
    }

    protected function setCsrf(string $key): string
    {
        $csrf = uniqid('', true);

        try {
            /** @var TokenStorageInterface $tokenStorage */
            $tokenStorage = self::getContainer()->get(TokenStorageInterface::class);

            $tokenStorage->setToken($key, $csrf);
        } catch (\Exception $exception) {
            throw new \RuntimeException($exception);
        }

        return $csrf;
    }
}
