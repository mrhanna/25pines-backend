<?php

namespace App\Hal;

use App\Utility\HalJson;
use App\Utility\HalStrategy;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AbstractContentStrategy implements HalStrategy
{
    public static function full() {
        return function(HalJson &$hj, $ac) {
            $hj->link('self', $this->router->generate('showContent', ['uuid' => $ac->getUuid()]));
        };
    }

    public static function concise() {
        return self::full();
    }
}
