<?php

namespace App\Hal;

use App\Utility\HalJson;
use App\Utility\HalStrategy;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class VideoStrategy implements HalStrategy
{
    public static function full() {
        return function(HalJson &$hj, $v) {
            $hj->link('self', $this->generateUrl('showVideo', ['uuid' => $v->getContent()->getUuid(), 'id' => $v->getId()]));
            $hj->link('media', $this->generateUrl('showContent', ['uuid' => $v->getContent()->getUuid()]));
        };
    }

    public static function concise() {
        return function(HalJson &$hj, $v) {
            $hj->link('self', $this->generateUrl('showVideo', ['uuid' => $v->getContent()->getUuid(), 'id' => $v->getId()]));
        };
    }
}
