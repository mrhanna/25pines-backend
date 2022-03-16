<?php

namespace App\API\Hal;

use App\API\Utility\HalJson;
use App\API\Utility\HalStrategy;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SeriesStrategy implements HalStrategy
{
    public static function full() {
        return function(HalJson &$hj, $s) {
            $hj->link('self', $this->generateUrl('showSeries', ['uuid' => $s->getUuid()]));
            $hj->link('collections', $this->generateUrl('showAllSeries'));

            $hj->link('episodes', $this->generateUrl('showSeriesEpisodes', ['uuid' => $s->getUuid()]));
            $episodes = $s->getEpisodes();
            $hj->embedArray('episodes', $this->mapCreateConcise($episodes));

            $hj->link('tags', $this->generateUrl('showSeriesTags', ['uuid' => $s->getUuid()]));
            $tags = $s->getTags();
            $hj->embedArray('tags', $this->mapCreateConcise($tags));
        };
    }

    public static function concise() {
        return function(HalJson &$hj, $s) {
            $hj->link('self', $this->generateUrl('showSeries', ['uuid' => $s->getUuid()]));
        };
    }
}
