<?php

namespace App\API\Controller;

use App\API\Repository\AbstractStreamableContentRepository;
use App\API\Repository\VideoRepository;
use App\API\Utility\HalJsonFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VideoController extends AbstractController
{
    #[Route('media/{uuid}/videos', name: 'showContentVideos', methods: ['GET'])]
    public function showContentVideos(AbstractStreamableContentRepository $repo, HalJsonFactory $hjf, $uuid): Response
    {
        $content = $repo->findOneBy(['uuid' => $uuid]);
        $json = $hjf->createCollection('videos', $content->getVideos())
            ->link('self', $this->generateUrl('showContentVideos', ['uuid' => $uuid], 0));
        return $this->json($json);
    }

    #[Route('media/{uuid}/videos/{id}', name: 'showVideo', methods: ['GET'])]
    public function showVideo(VideoRepository $repo, HalJsonFactory $hjf, string $uuid, int $id): Response
    {
        $video = $repo->findOneBy(['id' => $id]);
        if ($uuid != $video->getContent()->getUuid()) throw $this->createNotFoundException();
        $json = $hjf->create($video);
        return $this->json($json);
    }
}
