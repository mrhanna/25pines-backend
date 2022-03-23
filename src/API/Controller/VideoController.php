<?php

namespace App\API\Controller;

use App\API\Entity\AbstractStreamableContent;
use App\API\Entity\Video;
use App\API\Repository\AbstractStreamableContentRepository;
use App\API\Repository\VideoRepository;
use App\API\Utility\HalJsonFactory;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VideoController extends AbstractController
{
    private $ascRepo;
    private $vRepo;
    private $hjf;
    private $vi;
    private $em;

    public function __construct(
        AbstractStreamableContentRepository $ascRepo,
        VideoRepository $vRepo,
        HalJsonFactory $hjf,
        ValidatorInterface $vi,
        ManagerRegistry $doctrine
    ) {
        $this->ascRepo      = $ascRepo;
        $this->vRepo        = $vRepo;
        $this->hjf          = $hjf;
        $this->vi           = $vi;
        $this->em           = $doctrine->getManager();
    }

    #[Route('media/{uuid}/videos', name: 'showContentVideos')]
    public function videoCollection(Request $req, string $uuid): Response
    {
        $content = $this->ascRepo->findOneBy(['uuid' => $uuid]);
        if (!$content) {
            throw $this->createNotFoundException('Resource not found.');
        }

        switch ($req->getMethod()) {
            case 'GET':
                return $this->readVideos($uuid);
                break;
            case 'POST':
                return $this->addVideo($req);
        }

        throw new MethodNotAllowedHttpException(['GET', 'POST'], $req->getMethod() . ' is not allowed at this endpoint.');
    }

    #[Route('media/{uuid}/videos/{id}', name: 'showVideo')]
    public function videoSingleton(Request $req, string $uuid, int $id): Response
    {
        $video = $this->vRepo->findOneBy(['id' => $id]);
        if (!$video || $uuid !== $video->getContent()->getUuid()) {
            throw $this->createNotFoundException('Resource not found.');
        }

        switch ($req->getMethod()) {
            case 'GET':
                return $this->readVideo($video);
                break;
            case 'DELETE':
                return $this->deleteVideo($video);
                break;
            case 'PUT':
            case 'PATCH':
                return $this->updateVideo($video);
        }

        throw new MethodNotAllowedHttpException(['GET', 'PUT', 'PATCH', 'DELETE'], $req->getMethod() . ' is not allowed at this endpoint.');
    }

    public function readVideos(AbstractStreamableContent $content): Response
    {
        $json = $this->hjf->createCollection('videos', $content->getVideos())
            ->link('self', $this->generateUrl('showContentVideos', ['uuid' => $content->getUuid()], 0))
            ->link('media', $this->generateUrl('showContent', ['uuid' => $content->getUuid()], 0));
        return $this->json($json);
    }

    public function addVideo(Request $req, AbstractStreamableContent $content): Response
    {
        $video = new Video();
        $video->setUrl($req->request->get('url'));
        $video->setQuality($req->request->get('quality'));
        $video->setVideoType($req->request->get('videoType'));
        $video->setContent($content);

        $errors = $this->vi->validate($video);
        if (count($errors) > 0) {
            throw new ValidationFailedException('value', $errors);
        }

        $this->em->persist($video);
        $this->em->persist($content);
        $this->em->flush();

        return $this->redirectToRoute('showContentVideos', ['uuid' => $content->getUuid()]);
    }

    public function readVideo(Video $video): Response
    {
        $json = $this->hjf->create($video);
        return $this->json($json);
    }

    public function deleteVideo(Video $video): Response
    {
        $this->em->remove($video);
        $this->em->flush();
        return new Response('', 204);
    }

    public function updateVideo(Request $req, Video $video): Response
    {
        if ($req->request->get('url')) {
            $video->setUrl($req->request->get('url'));
        }
        if ($req->request->get('quality')) {
            $video->setQuality($req->request->get('quality'));
        }
        if ($req->request->get('videoType')) {
            $video->setVideoType($req->request->get('videoType'));
        }

        $errors = $this->vi->validate($video);
        if (count($errors) > 0) {
            throw new ValidationFailedException('value', $errors);
        }

        $this->em->persist($video);
        $this->em->flush();
        return new Response('', 204);
    }
}
