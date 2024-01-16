<?php

namespace BackSystem\Base\Helper;

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
        $array = $collection->toArray();

        $collection->clear();

        foreach ($array as $value) {
            if (!$collection->contains($value)) {
                $collection->add($value);
            }
        }

        return $collection;
    }
}
