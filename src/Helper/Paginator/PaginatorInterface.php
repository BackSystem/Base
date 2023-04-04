<?php

namespace BackSystem\Base\Helper\Paginator;

use Doctrine\ORM\Query;

interface PaginatorInterface
{
    public function allowSort(string ...$fields): self;

    public function paginate(Query $query): void;
}
