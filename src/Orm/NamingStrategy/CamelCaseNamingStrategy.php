<?php

namespace BackSystem\Base\Orm\NamingStrategy;

use Doctrine\ORM\Mapping\NamingStrategy;

class CamelCaseNamingStrategy implements NamingStrategy
{
    public function classToTableName($className): string
    {
        return substr($className, strrpos($className, '\\') + 1);
    }

    public function propertyToColumnName($propertyName, $className = null): string
    {
        return $propertyName;
    }

    public function embeddedFieldToColumnName($propertyName, $embeddedColumnName, $className = null, $embeddedClassName = null): string
    {
        return $propertyName;
    }

    public function referenceColumnName(): string
    {
        return 'id';
    }

    public function joinColumnName($propertyName): string
    {
        return strtolower($propertyName).ucwords($this->referenceColumnName());
    }

    public function joinTableName($sourceEntity, $targetEntity, $propertyName = null): string
    {
        return strtolower($this->classToTableName($sourceEntity)).ucwords($this->classToTableName($targetEntity));
    }

    public function joinKeyColumnName($entityName, $referencedColumnName = null): string
    {
        return strtolower($this->classToTableName($entityName)).($referencedColumnName ?: ucwords($this->referenceColumnName()));
    }
}
