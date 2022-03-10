<?php

namespace App\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

use App\Entity\AbstractContent;
use App\Repository\AbstractContentRepository;

class ContentController extends AbstractController
{
    #[Route('/content/{uuid}', name: 'showContent', methods: ['GET'])]
    public function showContent(AbstractContentRepository $contentRepo, $uuid): Response
    {
          $contentEntity = $contentRepo->findOneBy(['uuid' => $uuid]);
          return $this->json($contentEntity->toArray());
    }

    /*#[Route('/content', name: 'addContent', methods: ['POST'])]
    public function addContent(ManagerRegistry $doctrine, Request $request): Response
    {
        $entityManager = $doctrine->getManager();

        $guid = Uuid::v4();

        $content = new Content();
        $content->setGuid($guid)
            ->setTitle($request->request->get('title'))
            ->setGenres($request->request->get('genres'))
            ->setThumbnail($request->request->get('thumbnail'))
            ->setReleaseDate(new \DateTime($request->request->get('releaseDate')))
            ->setDateAdded(new \DateTime($request->request->get('dateAdded')))
            ->setShortDescription($request->request->get('shortDescription'))
            ->setLongDescription($request->request->get('longDescription'))
            ->setMediaType($request->request->get('mediaType'))
            ->setDuration($request->request->get('duration'))
            ->setLanguage('en-US');

        $entityManager->persist($content);
        $entityManager->flush();


        return $this->redirectToRoute('showContent', ['guid' => $guid], 201);
    }*/
}
