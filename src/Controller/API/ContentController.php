<?php

namespace App\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

use App\Entity\AbstractContent;
use App\Repository\AbstractContentRepository;
use App\Entity\Factory\ContentFactory;

class ContentController extends AbstractController
{
    #[Route('/content/{uuid}', name: 'showContent', methods: ['GET'])]
    public function showContent(AbstractContentRepository $contentRepo, $uuid): Response
    {
          $contentEntity = $contentRepo->findOneBy(['uuid' => $uuid]);
          return $this->json($contentEntity->toArray());
    }

    #[Route('/content', name: 'addContent', methods: ['POST'])]
    public function addContent(ManagerRegistry $doctrine, Request $request): Response
    {
        $entityManager = $doctrine->getManager();

        $content = ContentFactory::createFromArray($request->request->all());

        $entityManager->persist($content);
        $entityManager->flush();


        return $this->redirectToRoute('showContent', ['uuid' => $content->getUuid()], 201);

        //return $this->json($content->toArray());
    }
}
