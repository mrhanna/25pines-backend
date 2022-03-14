<?php

namespace App\Hal;

use App\Utility\HalJson;
use App\Utility\HalStrategy;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SeriesStrategy implements HalStrategy
{
    public static function full() {
        return function(HalJson &$hj, $s) {
            $hj->link('self', $this->generateUrl('showSeries', ['uuid' => $s->getUuid()]));
            $hj->link('collections', $this->generateUrl('showAllSeries'));
            $hj->link('episodes', $this->generateUrl('showSeriesEpisodes', ['uuid' => $s->getUuid()]));

            $episodes = $s->getEpisodes();
            $epJsons = $this->mapCreateConcise($episodes);
            $hj->embedArray('episodes', $epJsons);
        };
    }

    public static function concise() {
        return function(HalJson &$hj, $s) {
            $hj->link('self', $this->generateUrl('showSeries', ['uuid' => $s->getUuid()]));
        };
    }
}
