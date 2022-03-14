<?php

namespace App\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Series;
use App\Repository\SeriesRepository;
use App\Utility\HalJsonFactory;

class SeriesController extends AbstractController
{
    #[Route('/series', name: 'showAllSeries', methods: ['GET'])]
    public function showAllSeries(SeriesRepository $repo, HalJsonFactory $hjf): Response
    {
        $series = $repo->findAll();
        $collection = $hjf->mapCreateConcise($series);
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
        $json = $hjf->mapCreateConcise($series->getEpisodes());
        return $this->json($json);
    }
}
