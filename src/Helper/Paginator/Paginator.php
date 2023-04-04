<?php

namespace BackSystem\Base\Helper\Paginator;

use Doctrine\ORM\Query;
use Symfony\Component\HttpFoundation\RequestStack;

class Paginator implements PaginatorInterface
{
    /**
     * @var array<string>
     */
    private array $sortableFields = [];

    public function __construct(private readonly \Knp\Component\Pager\PaginatorInterface $paginator, private readonly RequestStack $requestStack)
    {
    }

    public function paginate(Query $query): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $page = $request ? $request->query->getInt('page', 1) : 1;

        if ($page < 1) {
            throw new PageOutOfBoundException();
        }

        $pagination = $this->paginator->paginate($query, $page, $query->getMaxResults() ?: 10, [
            'sortFieldWhitelist' => $this->sortableFields,
            'filterFieldWhitelist' => [],
        ]);

        if ($pagination->getTotalItemCount() && ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage()) < $page) {
            throw new PageOutOfBoundException();
        }
    }

    public function allowSort(string ...$fields): self
    {
        $this->sortableFields = array_merge($this->sortableFields, $fields);

        return $this;
    }
}
