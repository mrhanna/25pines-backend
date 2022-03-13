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
                $videoJson = new HalJson($video->jsonSerialize());
                $hj->embedPush('videos', $videoJson);
            }
        };
    }

    public static function concise() {
    }
}
