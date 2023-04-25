<?php

namespace BackSystem\Base\Helper\Paginator;

use Doctrine\ORM\Query;
use Knp\Component\Pager\Pagination\PaginationInterface;

interface PaginatorInterface
{
    /** @phpstan-ignore-next-line */
    public function paginate(Query $query): PaginationInterface;
}
