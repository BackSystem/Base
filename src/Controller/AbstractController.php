<?php

namespace BackSystem\Base\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerExceptionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Util\StringUtil;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public static function getSubscribedServices(): array
    {
        $subscribedServices = parent::getSubscribedServices();

        $subscribedServices['doctrine.orm.default_entity_manager'] = '?'.EntityManagerInterface::class;
        $subscribedServices['translator.default'] = '?'.TranslatorInterface::class;
        $subscribedServices['event_dispatcher'] = '?'.EventDispatcherInterface::class;

        return $subscribedServices;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        if (!$this->container->has('doctrine.orm.default_entity_manager')) {
            throw new \LogicException('The DoctrineBundle is not registered in your application. Try running "composer require doctrine/orm".');
        }

        return $this->container->get('doctrine.orm.default_entity_manager');
    }

    protected function getTranslator(): TranslatorInterface
    {
        return $this->container->get('translator.default');
    }

    protected function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->container->get('event_dispatcher');
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function createActionForm(string $type, mixed $data, string $action, array $options = []): FormInterface
    {
        $options = array_merge($options, ['action' => $action]);

        $blockPrefix = StringUtil::fqcnToBlockPrefix($type) ?: '';

        $sessionFormDataName = 'formData['.$blockPrefix.']';

        $session = $this->getRequest()->getSession();
        $form = $this->createForm($type, $data, $options);

        if ($session->has($sessionFormDataName)) {
            $form->submit($session->get($sessionFormDataName));

            $session->remove($sessionFormDataName);
        }

        return $form;
    }

    protected function saveForm(FormInterface $form): void
    {
        $session = $this->getRequest()->getSession();

        $formData = [];

        foreach ($form->all() as $field) {
            $formData[$field->getName()] = $field->getViewData();
        }

        $session->set('formData['.$form->getName().']', $formData);
    }

    /**
     * @param array<string, mixed> $params
     */
    protected function redirectBack(string $route, array $params = []): RedirectResponse
    {
        $request = $this->getRequest();

        if ($request->server->has('HTTP_REFERER')) {
            return $this->redirect($request->server->get('HTTP_REFERER'));
        }

        return $this->redirectToRoute($route, $params);
    }

    protected function getRequest(): Request
    {
        try {
            return $this->container->get('request_stack')->getCurrentRequest();
        } catch (ContainerExceptionInterface $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }
}
