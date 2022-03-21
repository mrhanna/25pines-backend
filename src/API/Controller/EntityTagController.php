<?php

namespace App\API\Controller;

use App\API\Entity\AbstractContent;
use App\API\Entity\Tag;
use App\API\Repository\AbstractContentRepository;
use App\API\Repository\TagRepository;
use App\API\Utility\HalJsonFactory;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntityTagController extends AbstractController
{
    private $hjf;
    private $em;
    private $tagRepo;

    public function __construct(HalJsonFactory $hjf, TagRepository $tagRepo, ManagerRegistry $doctrine)
    {
        $this->hjf = $hjf;
        $this->em = $doctrine->getManager();
        $this->tagRepo = $tagRepo;
    }

    public function entityTagCollection(Request $req, AbstractContentRepository $repo, string $uuid, string $self, string $parent): Response
    {
        $content = $repo->findOneBy(['uuid' => $uuid]);
        if (!$content) {
            throw $this->createNotFoundException();
        }

        switch ($req->getMethod()) {
            case 'GET':
                return $this->readContentTags($content, $self, $parent);
                break;
            case 'POST':
                return $this->tagContent($req, $content, $self);
                break;
        }

        return $this->json(['message' => $req->getMethod() . ' is not allowed at this endpoint.'], 405);
    }

    public function entityTagSingleton(Request $req, AbstractContentRepository $repo, string $uuid, string $self, string $name): Response
    {
        $content = $repo->findOneBy(['uuid' => $uuid]);
        if (!$content) {
            throw $this->createNotFoundException();
        }

        if ($req->getMethod() === 'DELETE') {
            return $this->untagContent($content, $self, $name);
        }

        return $this->json(['message' => $req->getMethod() . ' is not allowed at this endpoint.'], 405);
    }


    public function readContentTags(AbstractContent $content, string $self, string $parent): Response
    {
        $tags = $this->hjf->createCollection('tags', $content->getTags())
            ->link('self', $this->generateUrl($self, ['uuid' => $content->getUuid()], 0))
            ->link('parent', $this->generateUrl($parent, ['uuid' => $content->getUuid()], 0));
        return $this->json($tags);
    }

    public function tagContent(Request $req, AbstractContent $content, string $self): Response
    {
        $name = $req->request->get('name');
        if (!$name) {
            return $this->json(['message' => 'name must be specified.'], 401);
        }

        $tag = $this->tagRepo->findOneBy(['name' => $name]);

        if (!$tag) {
            $tag = (new Tag())->setName($name);
            $this->em->persist($tag);
        }

        $content->addTag($tag);
        $this->em->persist($content);
        $this->em->flush();

        return $this->redirectToRoute($self, ['uuid' => $content->getUuid()], 201);
    }

    public function untagContent(AbstractContent $content, string $self, string $name): Response
    {
        $tag = $this->tagRepo->findOneBy(['name' => $name]);

        if (!$tag) {
            throw $this->createNotFoundException(); // maybe??
        }

        $content->removeTag($tag);
        $this->em->persist($content);
        $this->em->flush();

        return $this->redirectToRoute($self, ['uuid' => $content->getUuid()]);
    }
}
