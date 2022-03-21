<?php

namespace App\API\Hal;

use App\API\Utility\HalJson;
use App\API\Utility\HalStrategy;

class SeriesStrategy implements HalStrategy
{
    public static function full(): ?callable
    {
        return function (HalJson &$hj, $s): void {
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

    public static function concise(): ?callable
    {
        return function (HalJson &$hj, $s): void {
            $hj->link('self', $this->generateUrl('showSeries', ['uuid' => $s->getUuid()]));
        };
    }
}
