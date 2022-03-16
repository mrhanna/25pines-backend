<?php

namespace App\API\Hal;

use App\API\Utility\HalJson;
use App\API\Utility\HalStrategy;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AbstractContentStrategy implements HalStrategy
{
    public static function full() {
        return function(HalJson &$hj, $ac) {
            $hj->link('self', $this->generateUrl('showContent', ['uuid' => $ac->getUuid()]));
        };
    }

    public static function concise() {
        return self::full();
    }
}
