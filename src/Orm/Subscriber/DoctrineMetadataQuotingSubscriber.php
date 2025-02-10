<?php

namespace BackSystem\Base\Orm\Subscriber;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

final class DoctrineMetadataQuotingSubscriber
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
    {
        $classMetadata = $args->getClassMetadata();

        // Quote schema and table names
        if (!empty($classMetadata->table['schema'])) {
            $classMetadata->table['schema'] = $this->quote($classMetadata->table['schema']);
        }

        if (!empty($classMetadata->table['name'])) {
            $classMetadata->table['name'] = $this->quote($classMetadata->table['name']);
        }

        // Quote field column names
        foreach ($classMetadata->fieldMappings as &$field) {
            if (isset($field['columnName'])) {
                $field['columnName'] = $this->quote($field['columnName']);
            }
        }

        // Quote field column names
        // foreach ($classMetadata->columnNames as $key => $value) {
        //     $classMetadata->columnNames[$key] = $this->quote($value);
        // }

        // Quote association join column names
        // foreach ($classMetadata->associationMappings as $associationKey => $association) {
        //     if (isset($association['joinColumns'])) {
        //         foreach ($association['joinColumns'] as $joinColumn) {
        //             $joinColumn->name = $this->quote($joinColumn->name);
        //         }
        //
        //         dump($association);
        //     }
        // }
    }

    private function quote(string $value): string
    {
        return '`'.trim($value, '`').'`';
    }
}
