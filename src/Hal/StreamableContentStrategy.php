<?php

namespace App\Hal;

use App\Utility\HalJson;
use App\Utility\HalStrategy;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StreamableContentStrategy implements HalStrategy
{
    public static function full() {
        return function(HalJson &$hj, $sc) {
            $videos = $sc->getVideos();

            foreach ($videos as $video) {
                $videoJson = $this->createConcise($video);
                $hj->embedPush('videos', $videoJson);
            }

            $hj->link('videos', $this->generateUrl('showContentVideos', ['uuid' => $sc->getUuid()]));
        };
    }

    public static function concise() {
    }
}
