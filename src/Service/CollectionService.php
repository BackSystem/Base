<?php

namespace BackSystem\Base\Service;

use BackSystem\Base\Helper\CollectionHelper;
use Doctrine\Common\Collections\Collection;

class CollectionService
{
    /**
     * @template T
     * @template TKey of array-key
     *
     * @param Collection<TKey, T> $collection
     *
     * @return Collection<TKey, T>
     */
    public function unique(Collection $collection): Collection
    {
        return CollectionHelper::unique($collection);
    }
}
