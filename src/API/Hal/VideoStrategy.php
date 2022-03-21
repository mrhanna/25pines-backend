<?php

namespace App\API\Hal;

use App\API\Utility\HalJson;
use App\API\Utility\HalStrategy;

class VideoStrategy implements HalStrategy
{
    public static function full(): ?callable
    {
        return function (HalJson &$hj, $v): void {
            $hj->link('self', $this->generateUrl('showVideo', ['uuid' => $v->getContent()->getUuid(), 'id' => $v->getId()]));
            $hj->link('media', $this->generateUrl('showContent', ['uuid' => $v->getContent()->getUuid()]));
        };
    }

    public static function concise(): ?callable
    {
        return function (HalJson &$hj, $v): void {
            $hj->link('self', $this->generateUrl('showVideo', ['uuid' => $v->getContent()->getUuid(), 'id' => $v->getId()]));
        };
    }
}
