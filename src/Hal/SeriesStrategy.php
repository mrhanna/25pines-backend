<?php

namespace App\Hal;

use App\Utility\HalJson;
use App\Utility\HalStrategy;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SeriesStrategy implements HalStrategy
{
    public static function full() {
        return function(HalJson &$hj, $s) {
            $hj->link('episodes', $this->generateUrl('showSeriesEpisodes', ['uuid' => $s->getUuid()]));

            $episodes = $s->getEpisodes();

            foreach ($episodes as $episode) {
                $epJson = $this->createConcise($episode);
                $hj->embedPush('episodes', $epJson);
            }
        };
    }

    public static function concise() {

    }
}
