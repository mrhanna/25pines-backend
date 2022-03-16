<?php

namespace App\API\Hal;

use App\API\Utility\HalJson;
use App\API\Utility\HalStrategy;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EpisodeStrategy implements HalStrategy
{
    public static function full() {
        return function(HalJson &$hj, $ep) {
            $series = $ep->getSeries();
            $seriesJson = $this->createConcise($series);
            $collectionUrl = $this->generateUrl('showSeriesEpisodes', ['uuid' => $series->getUuid()]);
            $seriesUrl = $this->generateUrl('showSeries', ['uuid' => $series->getUuid()]);
            $hj->link('collection', $collectionUrl);
            $hj->link('series', $seriesUrl);
            //$hj->embed('series', $seriesJson);
            $hj->link('self', $this->generateUrl('showContent', ['uuid' => $ep->getUuid()]));
        };
    }

    public static function concise() {

    }
}
