<?php

namespace App\RokuDP;

use App\API\Entity\AbstractContent;
use App\API\Entity\AbstractStreamableContent;
use App\API\Entity\Episode;
use App\API\Entity\Series;
use App\API\Entity\Tag;
use App\API\Entity\Video;
use App\API\Repository\SeriesRepository;
use App\API\Repository\StreamableContentRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RokuFeedGenerator extends AbstractController
{
    public const ROKU_GENRES = ['action', 'adventure', 'animals', 'animated', 'anime', 'children', 'comedy', 'crime', 'documentary', 'drama',
        'educational', 'fantasy', 'faith', 'food', 'fashion', 'gaming', 'health', 'history', 'horror', 'miniseries', 'mystery', 'nature',
        'news', 'reality', 'romance', 'science', 'sciencefiction', 'sitcom', 'special', 'sports', 'thriller', 'technology'];

    #[Route('/feed.json', name: 'generateRokuFeed', methods: ['GET'])]
    public function generateRokuFeed(StreamableContentRepository $scr, SeriesRepository $sr): Response
    {
        $movies             = $scr->findBy(['mediaType' => 'movie']);
        $series             = $sr->findAll();
        $shortFormVideos    = $scr->findBy(['mediaType' => 'shortFormVideo']);
        $tvSpecials         = $scr->findBy(['mediaType' => 'tvSpecial']);

        // TODO: decouple this, store in database?
        $feed = [
            'providerName' => 'LensAudio, LLC',
            'lastUpdated' => (new \DateTimeImmutable())->format(\DateTimeInterface::ISO8601),
            'language' => 'en-US',
        ];

        self::prepareContent($feed, 'movies', $movies);
        self::prepareContent($feed, 'series', $series);
        self::prepareContent($feed, 'shortFormVideos', $shortFormVideos);
        self::prepareContent($feed, 'tvSpecials', $tvSpecials);

        return $this->json($feed);
    }

    private function prepareContent(array &$feed, string $name, Collection $collection): void
    {
        foreach ($collection as $item) {
            $json = self::prepareContentParent($item);
            if ($json) {
                $feed[$name][] = $json;
            }
        }
    }

    private function prepareContentParent(AbstractContent $ac): array
    {
        $r['id']                = $ac->getUuid();
        $r['title']             = $ac->getTitle();
        $r['thumbnail']         = $ac->getThumbnail();
        $r['releaseDate']       = $ac->getReleaseDate()->format(\DateTimeInterface::ISO8601);
        $r['shortDescription']  = $ac->getShortDescription();
        $r['longDescription']   = $ac->getLongDescription();

        // Roku Episodes do not have genres or tags
        if (!($ac instanceof Episode)) {
            $r['genres']        = self::makeGenres($ac);
            $r['tags']          = array_map(fn(Tag $tag) => $tag->getName(), $ac->getTags()->toArray());
        }

        if ($ac instanceof Series) {
            $episodesContainer = self::prepareEpisodes($ac);
            if (!$episodesContainer) {
                return [];
            }
            $r = array_merge($r, $episodesContainer);
        } else {
            $r['rating']        = ['rating' => 'UNRATED'];  //note - Roku series do not have ratings.
            $r['content'] = self::prepareContentChild($ac);
            if (!$r['content']) {
                return [];
            }
        }

        return $r;
    }

    private function prepareEpisodes(Series $s): array
    {
        $eps = $s->getEpisodes();

        // associative array : seasonNumber => episode object
        $seasons = [];
        // episode number counter
        $i = 0;

        foreach ($eps as $ep) {
            // episodes are prepared like their parent series
            $epJson = self::prepareContentParent($ep);

            // essentially, this episode will be skipped if invalid.
            if ($epJson) {
                $epJson['episodeNumber'] = $i++;
                // map the episode to its season number
                $seasons[$ep->getSeasonNumber() ?? 0][] = $epJson;
            }
        }

        if (!$seasons) {
            return [];
        }

        // convert the seasons array from associative to regular
        // if there is only one key, a seasons array isn't necessary at all.
        if (count($seasons) === 1) {
            return ['episodes' => array_values($seasons)[0]];
        }

        $r = [];

        foreach ($seasons as $k => $v) {
            $r[] = [
                'seasonNumber' => $k,
                'episodes' => $v,
            ];
        }

        return ['seasons' => $r];
    }

    private function prepareContentChild(AbstractStreamableContent $sc): array
    {
        $r['dateAdded']         = $sc->getDateAdded()->format(\DateTimeInterface::ISO8601);
        $r['videos']            = []; // TODO
        $r['duration']          = $sc->getDuration();
        //$r['captions'] eventually! TODO
        //$r['trickPlayFiles'] eventually!
        $r['language']          = $sc->getLanguage();
        //$r[\adBreaks] eventually!

        $videos = $sc->getVideos();
        foreach ($videos as $video) {
            $r['videos'][] = self::prepareVideo($video);
        }

        if (!$r['videos']) {
            return [];
        }

        return $r;
    }

    private function prepareVideo(Video $v): array
    {
        $r['url']               = $v->getUrl();
        $r['quality']           = $v->getQuality();
        $r['videoType']         = $v->getVideoType();

        return $r;
    }

    function makeGenres(AbstractContent $ac): array
    {
        return array_intersect($ac->getGenres(), self::ROKU_GENRES);
    }
}
