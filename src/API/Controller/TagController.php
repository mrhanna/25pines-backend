<?php

namespace App\API\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\API\Repository\TagRepository;
use App\API\Utility\HalJsonFactory;

class TagController extends AbstractController
{
    #[Route('/tags', name: 'showAllTags')]
    public function showAllTags(Request $req, TagRepository $repo, HalJsonFactory $hjf): Response
    {
        if ($req->getMethod() === 'GET') {
            $tags = $repo->findAll();
            $collection = $hjf->createCollection('tags', $tags)
                ->link('self', $this->generateUrl('showAllTags', [], 0));
            return $this->json($collection);
        }

        return $this->json(['message' => $req->getMethod().' is not allowed at this endpoint.'], 405);
    }

    #[Route('/tags/{name}', name: 'showTag')]
    public function showTag(Request $req, TagRepository $repo, HalJsonFactory $hjf, ManagerRegistry $doctrine, $name): Response
    {
        $tag = $repo->findOneBy(['name' => $name]);
        if (!$tag) throw $this->createNotFoundException();

        switch ($req->getMethod()) {
            case 'GET':
                $json = $hjf->create($tag);
                return $this->json($json);
                break;
            case 'DELETE':
                $em = $doctrine->getManager();
                $em->remove($tag);
                $em->flush();
                return new Response('', 204);
                break;
            case 'PUT':
            case 'PATCH':
                $newName = trim($req->request->get('name'));
                if (!$newName) return $this->json(['message' => 'name must be specified.'], 401);
                $tag->setName($newName);
                $em = $doctrine->getManager();
                $em->persist($tag);
                $em->flush();
                return new Response('', 204);
                break;
        }

        return $this->json(['message' => $req->getMethod().' is not allowed at this endpoint.'], 405);
    }


}
