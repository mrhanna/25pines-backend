<?php

namespace App\API\Controller;

use App\API\Entity\Video;
use App\API\Repository\AbstractStreamableContentRepository;
use App\API\Repository\VideoRepository;
use App\API\Utility\HalJsonFactory;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VideoController extends AbstractController
{
    #[Route('media/{uuid}/videos', name: 'showContentVideos', methods: ['GET'])]
    public function showContentVideos(AbstractStreamableContentRepository $repo, HalJsonFactory $hjf, $uuid): Response
    {
        $content = $repo->findOneBy(['uuid' => $uuid]);
        if (!$content) throw $this->createNotFoundException();

        $json = $hjf->createCollection('videos', $content->getVideos())
            ->link('self', $this->generateUrl('showContentVideos', ['uuid' => $uuid], 0))
            ->link('media', $this->generateUrl('showContent', ['uuid' => $uuid], 0));
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

    #[Route('/media/{uuid}/videos', name: 'addVideo', methods: ['POST'])]
    public function addVideo(ValidatorInterface $vi, AbstractStreamableContentRepository $repo, ManagerRegistry $doctrine, Request $request, string $uuid): Response
    {
        $content = $repo->findOneBy(['uuid' => $uuid]);
        if (!$content) throw $this->createNotFoundException();

        $video = new Video();
        $video->setUrl($request->request->get('url'));
        $video->setQuality($request->request->get('quality'));
        $video->setVideoType($request->request->get('videoType'));
        $video->setContent($content);

        $errors = $vi->validate($video);
        if (count($errors) > 0) {
            throw new ValidationFailedException('value', $errors);
        }

        $em = $doctrine->getManager();
        $em->persist($video);
        $em->persist($content);
        $em->flush();

        return $this->redirectToRoute('showContentVideos', ['uuid' => $uuid]);
    }
}
