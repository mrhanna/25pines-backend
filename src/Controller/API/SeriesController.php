<?php

namespace App\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Episode;
use App\Entity\Series;
use App\Repository\SeriesRepository;
use App\Utility\HalJson;

class SeriesController extends AbstractController
{
    #[Route('/series', name: 'showAllSeries')]
    public function showAllSeries(SeriesRepository $repo): Response
    {
        $series = $repo->findAll();
        $collection = array_map(
            function (Series $s) {
                $json = new HalJson($s->conciseSerialize());
            }
        );
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/SeriesController.php',
        ]);
    }
}
