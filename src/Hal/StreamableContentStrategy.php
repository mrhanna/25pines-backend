<?php

namespace App\Hal;

use App\Utility\HalJson;
use App\Utility\HalStrategy;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StreamableContentStrategy implements HalStrategy
{
    public static function full() {
        return function(HalJson &$hj, $s) {
            $hj->link('tags', $this->generateUrl('showContentTags', ['uuid' => $s->getUuid()]));
            $tags = $s->getTags();
            $hj->embedArray('tags', $this->mapCreateConcise($tags));
        };
    }

    public static function concise() {

    }
}
