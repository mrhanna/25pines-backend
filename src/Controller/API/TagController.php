<?php

namespace App\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

use App\Repository\AbstractContentRepository;
use App\Repository\TagRepository;
use App\Utility\HalJsonFactory;

class TagController extends AbstractController
{
    #[Route('/tags', name: 'showAllTags', methods: ['GET'])]
    public function showAllTags(TagRepository $repo, HalJsonFactory $hjf): Response
    {
        $tags = $repo->findAll();
        $collection = $hjf->createCollection('tags', $tags)
            ->link('self', $this->generateUrl('showAllTags', [], 0));
        return $this->json($collection);
    }

    #[Route('/tags/{name}', name: 'showTag', methods: ['GET'])]
    public function showTag(TagRepository $repo, HalJsonFactory $hjf, $name): Response
    {
        $tag = $repo->findOneBy(['name' => $name]);
        $json = $hjf->create($tag);
        return $this->json($json);
    }
}
