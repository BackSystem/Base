<?php

namespace BackSystem\Base\Helper\Paginator;

use Doctrine\ORM\Query;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * @template TKey
 * @template TValue
 */
interface PaginatorInterface
{
    /**
     * @return PaginationInterface<TKey, TValue>
     */
    public function paginate(Query $query): PaginationInterface;

    /**
     * @return Paginator<TKey, TValue>
     */
    public function allowSort(string ...$fields): self;
}
