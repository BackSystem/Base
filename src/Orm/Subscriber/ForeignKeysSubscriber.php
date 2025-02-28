<?php

namespace BackSystem\Base\Orm\Subscriber;

use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

final class ForeignKeysSubscriber
{
    public function postGenerateSchema(GenerateSchemaEventArgs $args): void
    {
        $entityManager = $args->getEntityManager();

        $defaultSchemaName = $entityManager->getConnection()->getDatabase();

        if (!$defaultSchemaName) {
            throw new \RuntimeException('Default schema name not set.');
        }

        $schema = $args->getSchema();

        foreach ($entityManager->getMetadataFactory()->getAllMetadata() as $metaData) {
            $schemaName = $metaData->getSchemaName();

            // This is an entity on another database, we don't want to handle it
            if ($schemaName && $schemaName !== $defaultSchemaName) {
                continue;
            }

            // Fetch all relations of the entity
            foreach ($metaData->associationMappings as $mapping) {
                $targetMetaData = $entityManager->getClassMetadata($mapping['targetEntity']);
                $targetSchemaName = $targetMetaData->getSchemaName() ?: $defaultSchemaName;

                // The relation is on the same schema, so no problem here
                if ($targetSchemaName === $defaultSchemaName) {
                    continue;
                }

                if (!empty($mapping['joinTable'])) {
                    foreach ($mapping['joinTable']['inverseJoinColumns'] as $inverseColumn) {
                        $options = ['onUpdate' => 'cascade'];

                        if (!empty($inverseColumn['onDelete'])) {
                            $options['onDelete'] = $inverseColumn['onDelete'];
                        }

                        $foreignTable = $targetSchemaName.'.'.$targetMetaData->getTableName();

                        // Add the foreign key
                        $schema->getTable($mapping['joinTable']['name'])->addForeignKeyConstraint($foreignTable, [$inverseColumn['name']], [$inverseColumn['referencedColumnName']], $options);
                    }
                } elseif (!empty($mapping['joinColumns'])) {
                    foreach ($mapping['joinColumns'] as $joinColumn) {
                        $options = ['onUpdate' => 'cascade'];

                        if (!empty($joinColumn['onDelete'])) {
                            $options['onDelete'] = $joinColumn['onDelete'];
                        }

                        $foreignTable = $targetSchemaName.'.'.$targetMetaData->getTableName();

                        // Add the foreign key
                        $schema->getTable($metaData->getTableName())->addForeignKeyConstraint($foreignTable, [$joinColumn['name']], [$joinColumn['referencedColumnName']], $options);
                    }
                }
            }
        }
    }
}
