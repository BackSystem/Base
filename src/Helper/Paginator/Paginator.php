<?php

namespace BackSystem\Base\Helper\Paginator;

use Doctrine\ORM\Query;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class Paginator implements PaginatorInterface
{
    public function __construct(private readonly \Knp\Component\Pager\PaginatorInterface $paginator, private readonly RequestStack $requestStack)
    {
    }

    /** @phpstan-ignore-next-line */
    public function paginate(Query $query): PaginationInterface
    {
        $request = $this->requestStack->getCurrentRequest();
        $page = $request ? $request->query->getInt('page', 1) : 1;

        if ($page < 1) {
            throw new PageOutOfBoundException();
        }

        $pagination = $this->paginator->paginate($query, $page, $query->getMaxResults() ?: 10, [
            'sortFieldWhitelist' => [],
            'filterFieldWhitelist' => [],
        ]);

        if ($pagination->getTotalItemCount() && ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage()) < $page) {
            throw new PageOutOfBoundException();
        }

        return $pagination;
    }
}
