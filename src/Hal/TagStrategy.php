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
            $hj->link('series', $this->generateUrl('showAllSeries', ['tag' => $t->getName()]));
            $hj->link('media', $this->generateUrl('showAllContent', ['tag' => $t->getName()]));
        };
    }

    public static function concise() {
        return function(HalJson &$hj, $t) {
            $hj->link('self', $this->generateUrl('showTag', ['name' => $t->getName()]));
            $hj->link('series', $this->generateUrl('showAllSeries', ['tag' => $t->getName()]));
            $hj->link('media', $this->generateUrl('showAllContent', ['tag' => $t->getName()]));
        };
    }
}
