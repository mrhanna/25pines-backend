<?php

namespace App\API\Controller;

use App\API\Repository\TagRepository;
use App\API\Utility\HalJsonFactory;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Routing\Annotation\Route;

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

        throw new MethodNotAllowedHttpException(['GET'], $req->getMethod() . ' is not allowed at this endpoint.');
    }

    #[Route('/tags/{name}', name: 'showTag')]
    public function showTag(Request $req, TagRepository $repo, HalJsonFactory $hjf, ManagerRegistry $doctrine, string $name): Response
    {
        $tag = $repo->findOneBy(['name' => $name]);
        if (!$tag) {
            throw $this->createNotFoundException('Resource not found.');
        }

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
                if (!$newName) {
                    return $this->json(['message' => 'name must be specified.'], 401);
                }
                $tag->setName($newName);
                $em = $doctrine->getManager();
                $em->persist($tag);
                $em->flush();
                return new Response('', 204);
                break;
        }

        throw new MethodNotAllowedHttpException(['GET', 'PUT', 'PATCH', 'DELETE'], $req->getMethod() . ' is not allowed at this endpoint.');
    }
}
