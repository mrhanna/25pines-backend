<?php

namespace App\Hal;

use App\Utility\HalJson;
use App\Utility\HalStrategy;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EpisodeStrategy implements HalStrategy
{
    public static function full() {
        return function(HalJson &$hj, $ep) {
            $series = $ep->getSeries();
            $seriesJson = $this->createConcise($series);
            $seriesUrl = $this->router->generate('showContent', ['uuid' => $series->getUuid()]);
            $hj->link('series', $seriesUrl);
            $hj->embed('series', $seriesJson);
            $hj->link('self', $this->router->generate('showContent', ['uuid' => $ep->getUuid()]));
        };
    }

    public static function concise() {

    }
}
