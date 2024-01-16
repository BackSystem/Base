<?php

namespace BackSystem\Base\Queue;

use BackSystem\Base\Queue\Message\ServiceMethodMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class ScheduledTask
{
    public function __construct(private readonly Envelope $envelope, private readonly int $id, private readonly \DateTimeImmutable $createdAt)
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEnvelope(): Envelope
    {
        return $this->envelope;
    }

    public function getMessageClass(): string
    {
        return $this->envelope->getMessage()::class;
    }

    public function getMessage(): ServiceMethodMessage
    {
        /** @var ServiceMethodMessage $message */
        $message = $this->envelope->getMessage();

        return $message;
    }

    public function getScheduledAt(): \DateTimeInterface
    {
        /** @var ?DelayStamp $delay */
        $delay = $this->envelope->last(DelayStamp::class);

        if (!$delay) {
            return $this->createdAt;
        }

        $delaySeconds = $delay->getDelay() / 1000;

        return $this->createdAt->add(new \DateInterval("PT{$delaySeconds}S"));
    }
}
