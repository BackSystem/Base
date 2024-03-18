<?php

namespace BackSystem\Base\Orm\NamingStrategy;

use Doctrine\ORM\Mapping\NamingStrategy;

class CamelCaseNamingStrategy implements NamingStrategy
{
    public function classToTableName(string $className): string
    {
        return substr($className, strrpos($className, '\\') + 1);
    }

    public function propertyToColumnName(string $propertyName, $className = null): string
    {
        return $propertyName;
    }

    public function embeddedFieldToColumnName(string $propertyName, string $embeddedColumnName, ?string $className = null, ?string $embeddedClassName = null): string
    {
        return $propertyName;
    }

    public function referenceColumnName(): string
    {
        return 'id';
    }

    public function joinColumnName(string $propertyName, string $className): string
    {
        return strtolower($propertyName).ucwords($this->referenceColumnName());
    }

    public function joinTableName(string $sourceEntity, string $targetEntity, ?string $propertyName = null): string
    {
        return strtolower($this->classToTableName($sourceEntity)).ucwords($this->classToTableName($targetEntity));
    }

    public function joinKeyColumnName(string $entityName, ?string $referencedColumnName = null): string
    {
        return strtolower($this->classToTableName($entityName)).($referencedColumnName ?: ucwords($this->referenceColumnName()));
    }
}
