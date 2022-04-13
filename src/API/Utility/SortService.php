<?php

namespace App\API\Utility;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Collections\Expr\Comparison;

class SortService
{
    public static function applySortMapToCollection(array $sortMap, \Traversable $collection): void
    {
        $count = count($sortMap);
        // validate the map
        if ($count !== count($collection)) {
            throw new \Exception('sortMap length does not match item count');
        }

        $has = array_fill(0, $count, false);

        foreach ($sortMap as $index) {
            if ($index >= $count || $index < 0) {
                throw new \Exception('The index ' . $index . ' is out of bounds');
            }

            if ($has[$index]) {
                throw new \Exception('The index ' . $index . ' was reused.');
            }

            $has[$index] = true;
        }

        $i = 0;
        foreach ($collection as $item) {
            $item->setSort($sortMap[$i]);
            $i++;
        }
    }

    public static function removeIndexFromCollection(int $index, Selectable $collection): void
    {
        if ($index < 0) {
            throw new \InvalidArgumentException('index is out of bounds');
        }

        // decrement sort for higher sorted items
        $higherSortCriteria = Criteria::create()
            ->andWhere(new Comparison('sort', '>', $index));

        foreach ($collection->matching($higherSortCriteria) as $item) {
            $item->setSort($item->getSort() - 1);
        }
    }
}
