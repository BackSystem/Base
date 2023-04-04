<?php

namespace BackSystem\Base\Queue\Service;

use BackSystem\Base\Queue\FailedJob;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;
use Symfony\Component\Messenger\Transport\Sync\SyncTransport;
use Symfony\Component\Messenger\Transport\TransportInterface;

class FailedJobService
{
    private readonly ListableReceiverInterface $receiver;

    public function __construct(TransportInterface $receiver, private readonly MessageBusInterface $messageBus)
    {
        if (!($receiver instanceof ListableReceiverInterface)) {
            throw new \RuntimeException(sprintf('Service %s expects a receiver of type %s.', self::class, ListableReceiverInterface::class));
        }

        $this->receiver = $receiver;
    }

    /**
     * @return FailedJob[]
     */
    public function getJobs(): array
    {
        if ($this->receiver instanceof SyncTransport) {
            return [];
        }

        $envelopes = $this->receiver->all();

        if ($envelopes instanceof \Traversable) {
            $envelopes = iterator_to_array($envelopes);
        }

        return array_map(static fn (Envelope $envelope) => new FailedJob($envelope), $envelopes);
    }

    public function retryJob(int $jobId): void
    {
        $envelope = $this->receiver->find($jobId);

        if ($envelope instanceof Envelope) {
            $this->messageBus->dispatch($envelope->getMessage());
            $this->receiver->reject($envelope);
        } else {
            throw new \RuntimeException(sprintf('Unable to find the job #%d.', $jobId));
        }
    }

    public function deleteJob(int $jobId): void
    {
        $envelope = $this->receiver->find($jobId);

        if ($envelope instanceof Envelope) {
            $this->receiver->reject($envelope);
        }
    }
}
