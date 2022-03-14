<?php

namespace App\Controller\API;

use App\Repository\SeriesRepository;
use App\Utility\HalJsonFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SeriesController extends AbstractController
{
    #[Route('/series', name: 'showAllSeries', methods: ['GET'])]
    public function showAllSeries(SeriesRepository $repo, HalJsonFactory $hjf): Response
    {
        $series = $repo->findAll();
        $collection = $hjf->createCollection('series', $series)
            ->link('self', $this->generateUrl('showAllSeries', [], 0));

        return $this->json($collection);
    }

    #[Route('/series/{uuid}', name: 'showSeries', methods: ['GET'])]
    public function showSeries(SeriesRepository $repo, HalJsonFactory $hjf, string $uuid): Response
    {
        $series = $repo->findOneBy(['uuid' => $uuid]);
        return $this->json($hjf->create($series));
    }

    #[Route('/series/{uuid}/episodes', name: 'showSeriesEpisodes', methods: ['GET'])]
    public function showSeriesEpisodes(SeriesRepository $repo, HalJsonFactory $hjf, string $uuid): Response
    {
        $series = $repo->findOneBy(['uuid' => $uuid]);
        $collection = $hjf->createCollection('episodes', $series->getEpisodes())
            ->link('self', $this->generateUrl('showSeriesEpisodes', ['uuid' => $uuid], 0));
        return $this->json($collection);
    }
}
