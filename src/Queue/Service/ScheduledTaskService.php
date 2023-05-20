<?php

namespace BackSystem\Base\Queue\Service;

use BackSystem\Base\Queue\ScheduledTask;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class ScheduledTaskService
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly SerializerInterface $serializer)
    {
    }

    /**
     * @return ScheduledTask[]
     */
    public function getTasks(): array
    {
        try {
            $statement = $this->entityManager->getConnection()
                                             ->prepare('SELECT * FROM messenger_messages WHERE queue_name = ? AND created_at != available_at AND delivered_at IS NULL')
                                             ->executeQuery(['default']);

            $tasks = [];

            foreach ($statement->fetchAllAssociative() as $row) {
                $data = $this->serializer->decode(['body' => $row['body']]);
                $createdAt = \DateTimeImmutable::createFromMutable(new \DateTime($row['created_at']));

                $envelope = $data->with(new TransportMessageIdStamp($row['id']));

                $tasks[] = new ScheduledTask($envelope, $row['id'], $createdAt);
            }

            return $tasks;
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function deleteTask(int $id): void
    {
        // Todo: Soon
    }
}
