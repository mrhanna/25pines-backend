<?php

namespace App\API\Hal;

use App\API\Utility\HalJson;
use App\API\Utility\HalStrategy;

class TagStrategy implements HalStrategy
{
    public static function full(): ?callable
    {
        return function (HalJson &$hj, $t): void {
            $hj->link('self', $this->generateUrl('showTag', ['name' => $t->getName()]));
            $hj->link('collection', $this->generateUrl('showAllTags'));
            $hj->link('series', $this->generateUrl('showAllSeries', ['tag' => $t->getName()]));
            $hj->link('media', $this->generateUrl('showAllContent', ['tag' => $t->getName()]));
        };
    }

    public static function concise(): ?callable
    {
        return function (HalJson &$hj, $t): void {
            $hj->link('self', $this->generateUrl('showTag', ['name' => $t->getName()]));
            $hj->link('series', $this->generateUrl('showAllSeries', ['tag' => $t->getName()]));
            $hj->link('media', $this->generateUrl('showAllContent', ['tag' => $t->getName()]));
        };
    }
}
