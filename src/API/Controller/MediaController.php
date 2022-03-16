<?php

namespace App\API\Controller;

use App\API\Entity\Tag;
use App\API\Repository\AbstractStreamableContentRepository;
use App\API\Repository\StreamableContentRepository;
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
    public function showContent(AbstractStreamableContentRepository $repo, HalJsonFactory $hjf, string $uuid): Response
    {
          $content = $repo->findOneBy(['uuid' => $uuid]);
          if (!$content) throw $this->createNotFoundException();
          $json = $hjf->create($content);

          return $this->json($json);
    }

    #[Route('/media/{uuid}/tags', name: 'showContentTags', methods: ['GET'])]
    public function showContentTags(AbstractStreamableContentRepository $repo, HalJsonFactory $hjf, string $uuid): Response
    {
        $asc = $repo->findOneBy(['uuid' => $uuid]);
        if (!$asc) throw $this->createNotFoundException();
        $tags = $hjf->createCollection('tags', $asc->getTags())
            ->link('self', $this->generateUrl('showContentTags', ['uuid' => $uuid], 0))
            ->link('media', $this->generateUrl('showContent', ['uuid' => $uuid], 0));
        return $this->json($tags);
    }

    #[Route('/media', name: 'addContent', methods: ['POST'])]
    public function addContent(ValidatorInterface $vi, ManagerRegistry $doctrine, ContentFactory $cf, Request $request): Response
    {
        $entityManager = $doctrine->getManager();

        $content = $cf->createFromArray($request->request->all());
        $errors = $vi->validate($content);
        if ($errors) {
            throw new ValidationFailedException('value goes here?', $errors);
        }

        $entityManager->persist($content);
        $entityManager->flush();

        return $this->redirectToRoute('showContent', ['uuid' => $content->getUuid()], 201);
    }

    #[Route('/media/{uuid}/tags', name: 'tagMedia', methods: ['POST'])]
    public function tagContent(AbstractStreamableContentRepository $repo, TagRepository $tRepo, ManagerRegistry $doctrine, Request $request, string $uuid): Response
    {
        $content = $repo->findOneBy(['uuid' => $uuid]);
        if (!$content) throw $this->createNotFoundException();

        $em = $doctrine->getManager();

        $name = $request->request->get('name');
        if (!$name) throw new \Exception('No name was specified.');

        $tag = $tRepo->findOneBy(['name' => $name]);

        if (!$tag) {
            $tag = (new Tag())->setName($name);
            $em->persist($tag);
        }

        $content->addTag($tag);
        $em->persist($content);
        $em->flush();

        return $this->redirectToRoute('showContentTags', ['uuid' => $uuid]);
    }
}
