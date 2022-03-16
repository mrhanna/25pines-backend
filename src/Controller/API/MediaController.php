<?php

namespace App\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

use App\Repository\StreamableContentRepository;
use App\Repository\AbstractStreamableContentRepository;
use App\Utility\ContentFactory;
use App\Utility\HalJson;
use App\Utility\HalJsonFactory;

class MediaController extends AbstractController
{
    #[Route('/media', name: 'showAllContent', methods: ['GET'])]
    public function showAllContent(Request $req, StreamableContentRepository $repo, HalJsonFactory $hjf): Response
    {
        $tag = $req->query->get('tag');
        $content = $tag ? $repo->findAllWithTag($tag) : $repo->findAll();
        $collection = $hjf->createCollection('media', $content)
            ->link('self', $this->generateUrl('showAllContent', [], 0));

        return $this->json($collection);
    }

    #[Route('/media/{uuid}', name: 'showContent', methods: ['GET'])]
    public function showContent(AbstractStreamableContentRepository $contentRepo, HalJsonFactory $hjf, string $uuid): Response
    {
          $contentEntity = $contentRepo->findOneBy(['uuid' => $uuid]);
          $json = $hjf->create($contentEntity);

          return $this->json($json);
    }

    #[Route('/media/{uuid}/tags', name: 'showContentTags', methods: ['GET'])]
    public function showContentTags(AbstractStreamableContentRepository $repo, HalJsonFactory $hjf, string $uuid): Response
    {
        $asc = $repo->findOneBy(['uuid' => $uuid]);
        $tags = $hjf->createCollection('tags', $asc->getTags())
            ->link('self', $this->generateUrl('showContentTags', ['uuid' => $uuid], 0))
            ->link('media', $this->generateUrl('showContent', ['uuid' => $uuid], 0));
        return $this->json($tags);
    }



    #[Route('/media', name: 'addContent', methods: ['POST'])]
    public function addContent(ManagerRegistry $doctrine, ContentFactory $cf, Request $request): Response
    {
        $entityManager = $doctrine->getManager();

        $content = $cf->createFromArray($request->request->all());

        $entityManager->persist($content);
        $entityManager->flush();

        return $this->redirectToRoute('showContent', ['uuid' => $content->getUuid()], 201);
    }
}
