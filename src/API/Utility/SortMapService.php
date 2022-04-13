<?php

namespace App\API\Utility;

class SortMapService
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
}
