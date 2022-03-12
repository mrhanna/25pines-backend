<?php

namespace App\Utility;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\AbstractContent;
use App\Entity\Episode;
use App\Entity\Series;
use App\Entity\StreamableContent;
use App\Entity\Video;

class HalJsonFactory
{
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    public function create(\JsonSerializable $obj): HalJson
    {
        $hj = new HalJson($obj->jsonSerialize());

        if ($obj instanceof AbstractContent) self::setupAbstractContent($hj, $obj);
        if ($obj instanceof StreamableContent) self::setupStreamableContent($hj, $obj);
        if ($obj instanceof Episode) self::setupEpisode($hj, $obj);
        else if ($obj instanceof Series) self::setupSeries($hj, $obj);

        return $hj;
    }

    private function setupAbstractContent(HalJson &$hj, AbstractContent $ac): void
    {
        $hj->link('self', $this->router->generate('showContent', ['uuid' => $ac->getUuid()]));
    }

    private function setupStreamableContent(HalJson &$hj, StreamableContent $sc): void
    {
        $videos = $sc->getVideos();

        foreach ($videos as $video) {
            $videoJson = new HalJson($video->jsonSerialize());
            $hj->embedPush('videos', $videoJson);
        }
        /*
        if (count($videos) > 0) {
            $videoContainer = new HalJson();

            for ($i = 0; $i < count($videos); $i++) {
                $videoJson = $videos[$i]->jsonSerialize();
                $videoContainer->set($i, $videoJson);
                // TODO: do linking, too!
            }

            $hj->embed('videos', $videoContainer);
        }*/
    }

    private function setupEpisode(HalJson &$hj, Episode $ep): void
    {
        $series = $ep->getSeries();
        $seriesJson = new HalJson($series->conciseSerialize());
        $seriesUrl = $this->router->generate('showContent', ['uuid' => $seriesJson->get('uuid')]);

        $hj->link('series', $seriesUrl);

        $seriesJson->link('self', $seriesUrl);
        $hj->embed('series', $seriesJson);
    }

    private function setupSeries(HalJson &$hj, Series $s): void
    {
        $hj->link('episodes', $this->router->generate('showSeriesEpisodes', ['uuid' => $s->getUuid()]));

        $episodes = $s->getEpisodes();

        foreach ($episodes as $episode) {
            $epJson = new HalJson($episode->conciseSerialize());
            $epJson->link('self', $this->router->generate('showContent', ['uuid' => $episode->getUuid()]));
            $hj->embedPush('episodes', $epJson);
        }
    }
}
