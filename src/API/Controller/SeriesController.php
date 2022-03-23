<?php

namespace App\API\Controller;

use App\API\Repository\SeriesRepository;
use App\API\Utility\ContentFactory;
use App\API\Utility\HalJsonFactory;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SeriesController extends AbstractController
{
    #[Route('/series', name: 'showAllSeries')]
    public function seriesCollection(ContentCrudController $ccr, Request $req, SeriesRepository $repo): Response
    {
        return $ccr->collection(
            $req,
            $repo,
            self: 'showAllSeries',
            onCreate: 'showSeries',
            mediaType: 'series'
        );
    }

    #[Route('/series/{uuid}', name: 'showSeries')]
    public function seriesSingleton(ContentCrudController $ccr, Request $req, string $uuid, SeriesRepository $repo): Response
    {
        return $ccr->singleton($req, $uuid, $repo);
    }

    #[Route('/series/{uuid}/episodes', name: 'showSeriesEpisodes')]
    public function showSeriesEpisodes(Request $req, SeriesRepository $repo, string $uuid): Response
    {
        $series = $repo->findOneBy(['uuid' => $uuid]);
        if (!$series) {
            throw $this->createNotFoundException('Resource not found.');
        }

        $args = ['uuid' => $uuid];

        switch ($req->getMethod()) {
            case 'GET':
                return $this->forward(self::class . '::readEpisodes', $args);
                break;
            case 'POST':
                return $this->forward(self::class . '::createEpisode', $args);
                break;
        }

        throw new MethodNotAllowedHttpException(['GET', 'POST'], $req->getMethod() . ' is not allowed at this endpoint.');
    }

    public function readEpisodes(SeriesRepository $repo, HalJsonFactory $hjf, string $uuid): Response
    {
        $series = $repo->findOneBy(['uuid' => $uuid]);
        if (!$series) {
            throw $this->createNotFoundException('Resource not found.');
        }
        $collection = $hjf->createCollection('episodes', $series->getEpisodes())
            ->link('self', $this->generateUrl('showSeriesEpisodes', ['uuid' => $uuid], 0))
            ->link('series', $this->generateUrl('showSeries', ['uuid' => $uuid], 0));
        return $this->json($collection);
    }

    public function createEpisode(ValidatorInterface $vi, SeriesRepository $repo, ManagerRegistry $doctrine, ContentFactory $cf, Request $request, string $uuid): Response
    {
        $series = $repo->findOneBy(['uuid' => $uuid]);
        if (!$series) {
            throw $this->createNotFoundException('Resource not found.');
        }

        $entityManager = $doctrine->getManager();

        $content = $cf->createFromArray($request->request->all());
        $content->setMediaType('episode');
        $content->setSeries($series);

        $errors = $vi->validate($content);
        if (count($errors) > 0) {
            throw new ValidationFailedException('value goes here?', $errors);
        }

        $entityManager->persist($content);
        $entityManager->flush();

        return $this->redirectToRoute('showContent', ['uuid' => $content->getUuid()], 201);
    }

    #[Route('/series/{uuid}/tags', 'showSeriesTags')]
    public function showSeriesTags(EntityTagController $tc, Request $req, SeriesRepository $repo, string $uuid): Response
    {
        return $tc->entityTagCollection(
            $req,
            $repo,
            uuid: $uuid,
            self: 'showSeriesTags',
            parent: 'showSeries'
        );
    }

    #[Route('/series/{uuid}/tags/{name}', 'showSeriesTag')]
    public function showSeriesTag(EntityTagController $tc, Request $req, SeriesRepository $repo, string $name, string $uuid): Response
    {
        return $tc->entityTagSingleton(
            $req,
            $repo,
            uuid: $uuid,
            name: $name,
            self: 'showSeriesTags',
        );
    }
}
