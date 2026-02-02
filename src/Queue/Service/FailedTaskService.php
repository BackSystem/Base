<?php

namespace BackSystem\Base\Queue\Service;

use BackSystem\Base\Queue\FailedTask;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;
use Symfony\Component\Messenger\Transport\Sync\SyncTransport;
use Symfony\Component\Messenger\Transport\TransportInterface;

class FailedTaskService
{
    private readonly ListableReceiverInterface $receiver;

    public function __construct(TransportInterface $receiver, private readonly MessageBusInterface $messageBus)
    {
        if (!$receiver instanceof ListableReceiverInterface) {
            throw new \RuntimeException(sprintf('Service %s expects a receiver of type %s.', self::class, ListableReceiverInterface::class));
        }

        $this->receiver = $receiver;
    }

    /**
     * @return FailedTask[]
     */
    public function getTasks(): array
    {
        if ($this->receiver instanceof SyncTransport) {
            return [];
        }

        $envelopes = $this->receiver->all();

        if ($envelopes instanceof \Traversable) {
            $envelopes = iterator_to_array($envelopes);
        }

        return array_map(static fn (Envelope $envelope) => new FailedTask($envelope), $envelopes);
    }

    public function retryTask(int $taskId): void
    {
        $envelope = $this->receiver->find($taskId);

        if ($envelope instanceof Envelope) {
            $this->messageBus->dispatch($envelope->getMessage());
            $this->receiver->reject($envelope);
        } else {
            throw new \RuntimeException(sprintf('Unable to find the task #%d.', $taskId));
        }
    }

    public function deleteTask(int $taskId): void
    {
        $envelope = $this->receiver->find($taskId);

        if ($envelope instanceof Envelope) {
            $this->receiver->reject($envelope);
        }
    }
}
