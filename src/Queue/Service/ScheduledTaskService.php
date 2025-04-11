<?php

namespace BackSystem\Base\Queue\Service;

use BackSystem\Base\Queue\ScheduledTask;
use Doctrine\DBAL\ParameterType;
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
            $connection = $this->entityManager->getConnection();

            $statement = $connection->prepare('SELECT * FROM messenger_messages WHERE queue_name = :queueName AND created_at != available_at AND delivered_at IS NULL');
            $statement->bindValue('queueName', 'default', ParameterType::STRING);

            $result = $statement->executeQuery();

            $tasks = [];

            foreach ($result->fetchAllAssociative() as $row) {
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
