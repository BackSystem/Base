<?php

namespace BackSystem\Base\Orm\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class DatabaseSubscriber
{
    public function __construct(private readonly Security $security, private readonly EntityManagerInterface $entityManager)
    {
    }

    public function onFlush(OnFlushEventArgs $event): void
    {
        $entityManager = $event->getObjectManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityUpdates() as $object) {
            $hasUpdatedAt = method_exists($object, 'getUpdatedAt') && method_exists($object, 'setUpdatedAt');

            if ($hasUpdatedAt) {
                $object->setUpdatedAt(new \DateTimeImmutable());

                $meta = $entityManager->getClassMetadata(get_class($object));
                $unitOfWork->recomputeSingleEntityChangeSet($meta, $object);
            }
        }

        foreach ($unitOfWork->getScheduledEntityInsertions() as $object) {
            $hasCreatedBy = method_exists($object, 'getCreatedBy') && method_exists($object, 'setCreatedBy');
            $hasCreatedAt = method_exists($object, 'getCreatedAt') && method_exists($object, 'setCreatedAt');

            if ($hasCreatedBy) {
                if (null === $this->security->getUser()) {
                    continue;
                }

                /* @phpstan-ignore-next-line */
                $object->setCreatedBy($this->security->getUser());
            }

            $property = new PropertyAccessor();

            if ($hasCreatedAt && !$property->isReadable($object, 'createdAt')) {
                $object->setCreatedAt(new \DateTimeImmutable());
            }

            if ($hasCreatedBy || $hasCreatedAt) {
                $meta = $entityManager->getClassMetadata(get_class($object));
                $unitOfWork->recomputeSingleEntityChangeSet($meta, $object);
            }
        }

        foreach ($unitOfWork->getScheduledEntityDeletions() as $object) {
            $hasDeletedBy = method_exists($object, 'getDeletedBy') && method_exists($object, 'setDeletedBy');
            $hasDeletedAt = method_exists($object, 'getDeletedAt') && method_exists($object, 'setDeletedAt');

            if ($hasDeletedBy) {
                if (null === $this->security->getUser()) {
                    continue;
                }

                /* @phpstan-ignore-next-line */
                $object->setDeletedBy($this->security->getUser());
            }

            if ($hasDeletedAt) {
                if ($object->getDeletedAt() instanceof \DateTimeInterface) {
                    continue;
                }

                $object->setDeletedAt(new \DateTimeImmutable());
            }

            if ($hasDeletedBy || $hasDeletedAt) {
                $this->entityManager->persist($object);

                $meta = $entityManager->getClassMetadata(get_class($object));
                $unitOfWork->recomputeSingleEntityChangeSet($meta, $object);
            }
        }
    }
}
