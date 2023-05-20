<?php

namespace BackSystem\Base\Queue\Handler;

use BackSystem\Base\Queue\Message\ServiceMethodMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ServiceMethodMessageHandler
{
    public function __construct(private readonly ContainerInterface $container)
    {
    }

    public function __invoke(ServiceMethodMessage $message): void
    {
        /** @var callable $callable */
        $callable = [
            $this->container->get($message->getServiceName()),
            $message->getMethod(),
        ];

        call_user_func_array($callable, $message->getParams());
    }
}
