<?php

namespace BackSystem\Base\Controller;

use Psr\Container\ContainerExceptionInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Util\StringUtil;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
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
