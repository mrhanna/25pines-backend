<?php

namespace App\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

use App\Entity\AbstractContent;
use App\Entity\Episode;
use App\Entity\Series;
use App\Entity\Factory\ContentFactory;
use App\Repository\AbstractContentRepository;
use App\Repository\SeriesRepository;
use App\Utility\HalJsonFactory;

class MediaController extends AbstractController
{
    #[Route('/media', name: 'showAllContent', methods: ['GET'])]
    public function showAllContent(AbstractContentRepository $contentRepo, HalJsonFactory $hjf): Response
    {
        $contents = $contentRepo->findAll();
        $collection = $hjf->mapCreateConcise($contents);

        return $this->json($collection);
    }

    #[Route('/media/{uuid}', name: 'showContent', methods: ['GET'])]
    public function showContent(AbstractContentRepository $contentRepo, HalJsonFactory $hjf, $uuid): Response
    {
          $contentEntity = $contentRepo->findOneBy(['uuid' => $uuid]);
          $json = $hjf->create($contentEntity);

          return $this->json($json);
    }

    #[Route('/media', name: 'addContent', methods: ['POST'])]
    public function addContent(ManagerRegistry $doctrine, Request $request): Response
    {
        $entityManager = $doctrine->getManager();

        $content = ContentFactory::createFromArray($request->request->all());

        $entityManager->persist($content);
        $entityManager->flush();

        return $this->redirectToRoute('showContent', ['uuid' => $content->getUuid()], 201);
    }
}
