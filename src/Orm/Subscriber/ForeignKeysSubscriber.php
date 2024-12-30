<?php

namespace BackSystem\Base\Orm\Subscriber;

use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

final class ForeignKeysSubscriber
{
    public function __construct(private readonly bool $enabled)
    {
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        if (!$this->enabled) {
            return;
        }

        $classMetadata = $eventArgs->getClassMetadata();
        $table = $classMetadata->table;

        if (!isset($table['schema'])) {
            $schema = $this->getSchema($classMetadata->getName());

            if ($schema) {
                // Todo: Improve this
                if ('test' === $_ENV['APP_ENV']) {
                    $schema .= '_test';
                }

                $table['schema'] = $schema;
            }
        }

        $classMetadata->setPrimaryTable($table);
    }

    /**
     * Generate foreign keys to other databases.
     *
     * @throws SchemaException
     */
    public function postGenerateSchema(GenerateSchemaEventArgs $args): void
    {
        $entityManager = $args->getEntityManager();
        $schema = $args->getSchema();
        $mainSchemaName = $args->getSchema()->getName();

        foreach ($entityManager->getMetadataFactory()->getAllMetadata() as $metaData) {
            $schemaName = $metaData->getSchemaName();

            // Todo: Improve this
            if ('test' === $_ENV['APP_ENV']) {
                $schemaName .= '_test';
            }

            // This is an entity on another database, we don't want to handle it
            if ($schemaName && $schemaName !== $mainSchemaName) {
                continue;
            }

            // Fetch all relations of the entity
            foreach ($metaData->associationMappings as $mapping) {
                $targetMetaData = $entityManager->getClassMetadata($mapping['targetEntity']);
                $targetSchemaName = $targetMetaData->getSchemaName();

                if (null === $targetSchemaName) {
                    $targetSchemaName = $this->getSchema($targetMetaData->getName());
                }

                // // The relation is on the same schema, so no problem here
                // if (!$targetSchemaName || $targetSchemaName === $mainSchemaName) {
                //     continue;
                // }

                if (!empty($mapping['joinTable'])) {
                    foreach ($mapping['joinTable']['inverseJoinColumns'] as $inverseColumn) {
                        $options = ['onUpdate' => 'cascade'];

                        if (!empty($inverseColumn['onDelete'])) {
                            $options['onDelete'] = $inverseColumn['onDelete'];
                        }

                        $foreignTable = $targetSchemaName.'.'.$targetMetaData->getTableName();

                        // Add the foreign key
                        $schema->getTable($mapping['joinTable']['name'])
                            ->addForeignKeyConstraint(
                                $foreignTable,
                                [$inverseColumn['name']],
                                [$inverseColumn['referencedColumnName']],
                                $options
                            );
                    }
                } elseif (!empty($mapping['joinColumns'])) {
                    foreach ($mapping['joinColumns'] as $joinColumn) {
                        $options = ['onUpdate' => 'cascade'];

                        if (!empty($joinColumn['onDelete'])) {
                            $options['onDelete'] = $joinColumn['onDelete'];
                        }

                        $foreignTable = $targetSchemaName.'.'.$targetMetaData->getTableName();

                        // Add the foreign key
                        $schema->getTable($metaData->getTableName())
                            ->addForeignKeyConstraint(
                                $foreignTable,
                                [$joinColumn['name']],
                                [$joinColumn['referencedColumnName']],
                                $options
                            );
                    }
                }
            }
        }
    }

    private function getSchema(string $name): ?string
    {
        $split = explode('\\', $name);

        if (count($split) < 5) {
            return null;
        }

        if (!isset($split[1]) || 'Domain' !== $split[1]) {
            return null;
        }

        return (new CamelCaseToSnakeCaseNameConverter([$split[2]]))->normalize($split[2]);
    }
}
