<?php

namespace BackSystem\Base\Helper\Paginator;

use Knp\Component\Pager\Pagination\PaginationInterface;

interface PaginatorInterface
{
    /** @phpstan-ignore-next-line */
    public function paginate(mixed $query, ?int $limit = null): PaginationInterface;
}
