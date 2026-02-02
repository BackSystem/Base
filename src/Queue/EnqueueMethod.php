<?php

namespace BackSystem\Base\Queue;

use BackSystem\Base\Queue\Message\ServiceMethodMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class EnqueueMethod
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    /**
     * @psalm-template T of object
     *
     * @param array<int, mixed> $params
     *
     * @psalm-param class-string<T> $service
     * @psalm-param callable-string<T> $method
     */
    public function enqueue(string $service, string $method, array $params = [], ?\DateTimeInterface $date = null): void
    {
        $stamps = [];

        if (null !== $date) {
            $delay = 1000 * ($date->getTimestamp() - time());

            if ($delay > 0) {
                $stamps[] = new DelayStamp($delay);
            }
        }

        $this->bus->dispatch(new ServiceMethodMessage($service, $method, $params), $stamps);
    }
}
