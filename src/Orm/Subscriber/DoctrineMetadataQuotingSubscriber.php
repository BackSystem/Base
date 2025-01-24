<?php

namespace BackSystem\Base\Orm\Subscriber;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

final class DoctrineMetadataQuotingSubscriber
{
    public function __construct(private readonly bool $enabled)
    {
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
    {
        if (!$this->enabled) {
            return;
        }

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

        // Quote association join column names
        foreach ($classMetadata->associationMappings as &$association) {
            if (isset($association['joinColumns'])) {
                foreach ($association['joinColumns'] as &$joinColumn) {
                    $joinColumn['name'] = $this->quote($joinColumn['name']);
                }
            }
        }
    }

    private function quote(string $value): string
    {
        return '`'.trim($value, '`').'`';
    }
}
