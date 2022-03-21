<?php

namespace App\API\Controller;

use App\API\Repository\AbstractStreamableContentRepository;
use App\API\Repository\StreamableContentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MediaController extends AbstractController
{
    #[Route('/media', name: 'showAllContent')]
    public function mediaCollection(ContentCrudController $ccr, Request $req, StreamableContentRepository $repo): Response
    {
        return $ccr->collection(
            $req,
            $repo,
            self: 'showAllContent',
            onCreate: 'showContent',
        );
    }

    #[Route('/media/{uuid}', name: 'showContent')]
    public function mediaSingleton(ContentCrudController $ccr, Request $req, string $uuid, AbstractStreamableContentRepository $repo): Response
    {
        return $ccr->singleton($req, $uuid, $repo);
    }

    #[Route('/media/{uuid}/tags', 'showContentTags')]
    public function showContentTags(EntityTagController $tc, Request $req, AbstractStreamableContentRepository $repo, string $uuid): Response
    {
        return $tc->entityTagCollection(
            $req,
            $repo,
            uuid: $uuid,
            self: 'showContentTags',
            parent: 'showParent'
        );
    }

    #[Route('/media/{uuid}/tags/{name}', 'showContentTag')]
    public function showContentTag(EntityTagController $tc, Request $req, AbstractStreamableContentRepository $repo, string $name, string $uuid): Response
    {
        return $tc->entityTagSingleton(
            $req,
            $repo,
            uuid: $uuid,
            name: $name,
            self: 'showContentTag',
        );
    }
}
