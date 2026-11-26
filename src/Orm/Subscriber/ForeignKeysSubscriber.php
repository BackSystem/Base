<?php

namespace BackSystem\Base\Orm\Subscriber;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\ForeignKeyConstraint\ReferentialAction;
use Doctrine\DBAL\Schema\SchemaEditor;
use Doctrine\DBAL\Schema\TableEditor;
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

        $schemaEditor = $args->getSchema()->edit();

        foreach ($entityManager->getMetadataFactory()->getAllMetadata() as $metaData) {
            $schemaName = $metaData->getSchemaName();

            // Ignore entities belonging to another database
            if ($schemaName && $schemaName !== $defaultSchemaName) {
                continue;
            }

            foreach ($metaData->associationMappings as $mapping) {
                $targetMetaData = $entityManager->getClassMetadata($mapping['targetEntity']);
                $targetSchemaName = $targetMetaData->getSchemaName() ?: $defaultSchemaName;

                // Ignore relations within the same schema
                if ($targetSchemaName === $defaultSchemaName) {
                    continue;
                }

                if (!empty($mapping['joinTable'])) {
                    foreach ($mapping['joinTable']['inverseJoinColumns'] as $inverseColumn) {
                        $this->addForeignKey(
                            $schemaEditor,
                            $mapping['joinTable']['schema'] ?? null,
                            $mapping['joinTable']['name'],
                            $targetSchemaName,
                            $targetMetaData->getTableName(),
                            $inverseColumn['name'],
                            $inverseColumn['referencedColumnName'],
                            $inverseColumn['onDelete'] ?? null,
                        );
                    }
                } elseif (!empty($mapping['joinColumns'])) {
                    foreach ($mapping['joinColumns'] as $joinColumn) {
                        $this->addForeignKey(
                            $schemaEditor,
                            $schemaName,
                            $metaData->getTableName(),
                            $targetSchemaName,
                            $targetMetaData->getTableName(),
                            $joinColumn['name'],
                            $joinColumn['referencedColumnName'],
                            $joinColumn['onDelete'] ?? null,
                        );
                    }
                }
            }
        }

        $args->setSchema($schemaEditor->create());
    }

    private function addForeignKey(
        SchemaEditor $schemaEditor,
        ?string $schemaName,
        string $tableName,
        string $targetSchemaName,
        string $targetTableName,
        string $columnName,
        string $referencedColumnName,
        ?string $onDelete,
    ): void {
        $foreignKeyEditor = ForeignKeyConstraint::editor()
            ->setUnquotedReferencingColumnNames($columnName)
            ->setUnquotedReferencedTableName(
                $targetTableName,
                $targetSchemaName,
            )
            ->setUnquotedReferencedColumnNames($referencedColumnName)
            ->setOnUpdateAction(ReferentialAction::CASCADE);

        if (!empty($onDelete)) {
            $foreignKeyEditor->setOnDeleteAction(
                ReferentialAction::from(strtoupper($onDelete)),
            );
        }

        $schemaEditor->modifyTableByUnquotedName(
            $tableName,
            static function (TableEditor $tableEditor) use ($foreignKeyEditor): void {
                $tableEditor->addForeignKeyConstraint(
                    $foreignKeyEditor->create(),
                );
            },
            $schemaName,
        );
    }
}
