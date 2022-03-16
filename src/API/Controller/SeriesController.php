<?php

namespace App\API\Controller;

use App\API\Entity\Tag;
use App\API\Repository\SeriesRepository;
use App\API\Repository\TagRepository;
use App\API\Utility\ContentFactory;
use App\API\Utility\HalJsonFactory;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class SeriesController extends AbstractController
{
    #[Route('/series', name: 'showAllSeries', methods: ['GET'])]
    public function showAllSeries(Request $req, SeriesRepository $repo, HalJsonFactory $hjf): Response
    {
        $tag = $req->query->get('tag');
        $series = $tag ? $repo->findAllWithTag($tag) : $repo->findAll();
        $collection = $hjf->createCollection('series', $series)
            ->link('self', $this->generateUrl('showAllSeries', [], 0));

        return $this->json($collection);
    }

    #[Route('/series/{uuid}', name: 'showSeries', methods: ['GET'])]
    public function showSeries(SeriesRepository $repo, HalJsonFactory $hjf, string $uuid): Response
    {
        $series = $repo->findOneBy(['uuid' => $uuid]);
        if (!$series) throw $this->createNotFoundException();
        return $this->json($hjf->create($series));
    }

    #[Route('/series/{uuid}/episodes', name: 'showSeriesEpisodes', methods: ['GET'])]
    public function showSeriesEpisodes(SeriesRepository $repo, HalJsonFactory $hjf, string $uuid): Response
    {
        $series = $repo->findOneBy(['uuid' => $uuid]);
        if (!$series) throw $this->createNotFoundException();
        $collection = $hjf->createCollection('episodes', $series->getEpisodes())
            ->link('self', $this->generateUrl('showSeriesEpisodes', ['uuid' => $uuid], 0))
            ->link('series', $this->generateUrl('showSeries', ['uuid' => $uuid], 0));
        return $this->json($collection);
    }

    #[Route('/series/{uuid}/tags', name: 'showSeriesTags', methods: ['GET'])]
    public function showSeriesTags(SeriesRepository $repo, HalJsonFactory $hjf, string $uuid): Response
    {
        $series = $repo->findOneBy(['uuid' => $uuid]);
        if (!$series) throw $this->createNotFoundException();
        $tags = $hjf->createCollection('tags', $series->getTags())
            ->link('self', $this->generateUrl('showSeriesTags', ['uuid' => $uuid], 0))
            ->link('series', $this->generateUrl('showSeries', ['uuid' => $uuid], 0));
        return $this->json($tags);
    }

    #[Route('/media', name: 'addSeries', methods: ['POST'])]
    public function addSeries(ValidatorInterface $vi, ManagerRegistry $doctrine, ContentFactory $cf, Request $request): Response
    {
        $entityManager = $doctrine->getManager();

        $content = $cf->createFromArray($request->request->all());
        $content->setMediaType('series');
        $errors = $vi->validate($content);
        if ($errors) {
            throw new ValidationFailedException('value goes here?', $errors);
        }

        $entityManager->persist($content);
        $entityManager->flush();

        return $this->redirectToRoute('showSeries', ['uuid' => $content->getUuid()], 201);
    }

    #[Route('/series/{uuid}/episodes', name: 'addEpisode', methods: ['POST'])]
    public function addEpisode(ValidatorInterface $vi, SeriesRepository $repo, ManagerRegistry $doctrine, ContentFactory $cf, Request $request, string $uuid): Response
    {
        $series = $repo->findOneBy(['uuid' => $uuid]);
        if (!$series) throw $this->createNotFoundException();

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

    #[Route('/series/{uuid}/tags', name: 'tagSeries', methods: ['POST'])]
    public function tagSeries(SeriesRepository $repo, TagRepository $tRepo, ManagerRegistry $doctrine, Request $request, string $uuid): Response
    {
        $series = $repo->findOneBy(['uuid' => $uuid]);
        if (!$series) throw $this->createNotFoundException();

        $em = $doctrine->getManager();

        $name = $request->request->get('name');
        if (!$name) throw new \Exception('No name was specified.');

        $tag = $tRepo->findOneBy(['name' => $name]);

        if (!$tag) {
            $tag = (new Tag())->setName($name);
            $em->persist($tag);
        }

        $series->addTag($tag);
        $em->persist($series);
        $em->flush();

        return $this->redirectToRoute('showSeriesTags', ['uuid' => $uuid]);
    }
}
