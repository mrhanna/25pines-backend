<?php

namespace App\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

use App\Entity\AbstractContent;
use App\Entity\Episode;
use App\Entity\Series;
use App\Entity\Factory\ContentFactory;
use App\Repository\AbstractContentRepository;
use App\Repository\SeriesRepository;
use App\Utility\HalJson;
use App\Utility\HalJsonFactory;

class ContentController extends AbstractController
{
    #[Route('/content', name: 'showAllContent', methods: ['GET'])]
    public function showAllContent(AbstractContentRepository $contentRepo): Response
    {
        $contents = $contentRepo->findAll();
        $collection = array_map(
            function (AbstractContent $ac) {
                $json = new HalJson($ac->conciseSerialize());
                $json->link('self', $this->generateUrl('showContent', ['uuid' => $ac->getUuid()]));

                return $json;
            },
            $contents
        );

        return $this->json($collection);
    }

    #[Route('/content/{uuid}', name: 'showContent', methods: ['GET'])]
    public function showContent(AbstractContentRepository $contentRepo, HalJsonFactory $hjf, $uuid): Response
    {
          $contentEntity = $contentRepo->findOneBy(['uuid' => $uuid]);
          $json = $hjf->create($contentEntity, $this);

          return $this->json($json);
    }

    #[Route('/content/{uuid}/episodes', name: 'showSeriesEpisodes', methods: ['GET'])]
    public function showSeriesEpisodes(SeriesRepository $repo, string $uuid): Response
    {
        $series = $repo->findOneBy(['uuid' => $uuid]);
        $collection = array_map(
            function (Episode $episode) {
                return (new HalJson($episode->conciseSerialize()))
                    ->link('self', $this->generateUrl('showContent', ['uuid' => $episode->getUuid()]));
            },
            $series->getEpisodes()->toArray()
        );

        return $this->json($collection);
    }

    #[Route('/content', name: 'addContent', methods: ['POST'])]
    public function addContent(ManagerRegistry $doctrine, Request $request): Response
    {
        $entityManager = $doctrine->getManager();

        $content = ContentFactory::createFromArray($request->request->all());

        $entityManager->persist($content);
        $entityManager->flush();


        return $this->redirectToRoute('showContent', ['uuid' => $content->getUuid()], 201);

        //return $this->json($content->toArray());
    }
}
