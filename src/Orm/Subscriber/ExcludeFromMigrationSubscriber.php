<?php

namespace BackSystem\Base\Orm\Subscriber;

use BackSystem\Base\Orm\Attribute\ExcludeFromMigration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

final class ExcludeFromMigrationSubscriber
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $args): void
    {
        $schema = $args->getSchema();

        $defaultSchemaName = $this->entityManager->getConnection()->getDatabase();

        if (!$defaultSchemaName) {
            throw new \RuntimeException('Default schema name not set.');
        }

        $allMetadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

        foreach ($schema->getTables() as $table) {
            $split = explode('.', $table->getName());

            $schemaName = $defaultSchemaName;

            if (2 === count($split)) {
                [$schemaName, $tableName] = $split;
            } else {
                $tableName = $split[0];
            }

            $entityClassName = null;

            foreach ($allMetadata as $metadata) {
                $metadataTableName = trim($metadata->getTableName(), '`');
                $metadataSchemaName = trim($metadata->getSchemaName() ?: $defaultSchemaName, '`');

                if ($metadataTableName === $tableName && $metadataSchemaName === $schemaName) {
                    $entityClassName = $metadata->getName();

                    break;
                }
            }

            if ($entityClassName) {
                if ($schemaName !== $defaultSchemaName) {
                    $schema->dropTable($table->getName());

                    continue;
                }

                $reflectionClass = new \ReflectionClass($entityClassName);

                if ([] !== $reflectionClass->getAttributes(ExcludeFromMigration::class)) {
                    $schema->dropTable($table->getName());
                }
            }
        }
    }
}
