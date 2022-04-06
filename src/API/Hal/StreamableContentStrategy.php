<?php

namespace App\API\Hal;

use App\API\Utility\HalJson;
use App\API\Utility\HalStrategy;

class StreamableContentStrategy implements HalStrategy
{
    public static function full(): ?callable
    {
        return function (HalJson &$hj, $s): void {
            $hj->link('tags', $this->generateUrl('showContentTags', ['uuid' => $s->getUuid()]));
            // $tags = $s->getTags();
            // $hj->embedArray('tags', $this->mapCreateConcise($tags));
        };
    }

    public static function concise(): ?callable
    {
        return null;
    }
}
