<?php

namespace BackSystem\Base\Orm\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class DeletedFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        if ($targetEntity->hasField('deletedAt')) {
            return $targetTableAlias.'.deleted_at IS NULL';
        }

        return '';
    }
}
