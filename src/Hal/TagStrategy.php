<?php

namespace App\Hal;

use App\Utility\HalJson;
use App\Utility\HalStrategy;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TagStrategy implements HalStrategy
{
    public static function full() {
        return function(HalJson &$hj, $t) {
            $hj->link('self', $this->generateUrl('showTag', ['name' => $t->getName()]));
            $hj->link('collection', $this->generateUrl('showAllTags'));
        };
    }

    public static function concise() {
        return function(HalJson &$hj, $t) {
            $hj->link('self', $this->generateUrl('showTag', ['name' => $t->getName()]));
        };
    }
}
