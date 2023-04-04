<?php

namespace BackSystem\Base\Helper;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class CollectionHelper
{
    /**
     * @template T
     * @template TKey of array-key
     *
     * @param Collection<TKey, T> $collection
     *
     * @return Collection<TKey, T>
     */
    public static function unique(Collection $collection): Collection
    {
        return new ArrayCollection(array_unique($collection->toArray(), SORT_REGULAR));
    }
}
