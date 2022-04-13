<?php

namespace App\API\Controller;

use App\API\Entity\Playlist;
use App\API\Entity\PlaylistItem;
use App\API\Repository\AbstractContentRepository;
use App\API\Repository\PlaylistRepository;
use App\API\Repository\PlaylistItemRepository;
use App\API\Utility\HalJson;
use App\API\Utility\HalJsonFactory;
use App\API\Utility\SortService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PlaylistController extends AbstractController
{
    private $em;
    private AbstractContentRepository $acr;
    private HalJsonFactory $hjf;
    private PlaylistRepository $pr;
    private PlaylistItemRepository $pir;
    private ValidatorInterface $vi;

    public function __construct(
        AbstractContentRepository $acr,
        HalJsonFactory $hjf,
        ManagerRegistry $doctrine,
        PlaylistRepository $pr,
        PlaylistItemRepository $pir,
        ValidatorInterface $vi
    ) {
        $this->acr = $acr;
        $this->em = $doctrine->getManager();
        $this->hjf = $hjf;
        $this->pr = $pr;
        $this->pir = $pir;
        $this->vi = $vi;
    }

    #[Route('/playlists', name: 'showPlaylists', methods: ['GET', 'POST'])]
    public function showPlaylists(Request $req): Response
    {
        switch ($req->getMethod()) {
            case 'GET':
                return $this->readAllPlaylists();
                break;
            case 'POST':
                return $this->createPlaylist($req);
                break;
        }
    }

    #[Route('/playlists/{uuid}', name: 'showPlaylist', methods: ['GET', 'PUT', 'PATCH', 'DELETE'])]
    public function showPlaylist(Request $req, string $uuid): Response
    {
        $playlist = $this->pr->findOneBy(['uuid' => $uuid]);
        if (!$playlist) {
            throw $this->createNotFoundException('Playlist not found');
        }

        switch ($req->getMethod()) {
            case 'GET':
                return $this->readPlaylist($playlist);
                break;
            case 'PUT':
                $index = $req->request->get('index');
                $content = $req->request->get('uuid');

                if ($index !== null && $content !== null) {
                    return $this->insertIntoPlaylist($playlist, $content, $index);
                } else if ($content !== null) {
                    return $this->addToPlaylist($playlist, $content);
                } else if ($index !== null) {
                    return $this->removeFromPlaylist($playlist, $index);
                }

                throw new BadRequestException('index and/or mediaUuid must be specified');
                break;
            case 'PATCH':
                return $this->updatePlaylist($playlist, $req);
                break;
            case 'DELETE':
                return $this->deletePlaylist($playlist);
        }
    }

    private function readAllPlaylists(): Response
    {
        $playlists = $this->pr->findAll();

        $hj = new HalJson();
        $hj->link('self', $this->generateUrl('showPlaylists', [], 0));
        $embeds = [];

        foreach ($playlists as $pl) {
            $embed = new HalJson();
            $embed
                ->set('uuid', $pl->getUuid())
                ->set('name', $pl->getName())
                ->set('itemCount', $pl->getItems()->count())
                ->set('rokuCategorySetting', $pl->getRokuCategorySetting())
                ->link('self', $this->generateUrl(
                    'showPlaylist',
                    ['uuid' => $pl->getUuid()],
                    0
                ));
            $embeds[] = $embed;
        }

        $hj->embedArray('playlists', $embeds);

        return $this->json($hj);
    }

    private function createPlaylist(Request $req): Response
    {
        $name = $req->request->get('name');

        if (!$name) {
            throw new BadRequestException('name must be specified');
        }

        $pl = new Playlist();
        $pl->setName($name);
        $pl->setSort($this->pr->count([]));
        $pl->setRokuCategorySetting($req->request->get('rokuCategorySetting') ?? 'off');

        $errors = $this->vi->validate($pl);

        if (count($errors) > 0) {
            throw new ValidationFailedException('', errors);
        }

        $this->em->persist($pl);
        $this->em->flush();

        return $this->redirectToRoute(
            'showPlaylist',
            ['uuid' => $pl->getUuid()],
            201
        );
    }

    private function readPlaylist(Playlist $pl): Response
    {
        $contents = [];

        foreach ($pl->getItems() as $pli) {
            $contents[] = $pli->getContent();
        }

        $playlistJson = $this->hjf->createCollection('items', $contents);
        $playlistJson
            ->set('uuid', $pl->getUuid())
            ->set('name', $pl->getName())
            ->set('itemCount', $pl->getItems()->count())
            ->set('rokuCategorySetting', $pl->getRokuCategorySetting())
            ->link('self', $this->generateUrl(
                'showPlaylist',
                ['uuid' => $pl->getUuid()],
                0
            ))
            ->link('collection', $this->generateUrl('showPlaylists', [], 0));

        return $this->json($playlistJson);
    }

    private function deletePlaylist(Playlist $pl): Response
    {
        $this->em->remove($pl);
        SortService::removeIndexFromCollection($pl->getSort(), $this->pr);
        $this->em->flush();

        return new Response('', 204);
    }

    private function addToPlaylist(Playlist $pl, string $contentUuid): Response
    {
        $content = $this->acr->findOneBy(['uuid' => $contentUuid]);

        if (!$content) {
            throw $this->createNotFoundException('The content could not be found');
        }

        $playlistItem = new PlaylistItem();
        $playlistItem->setContent($content);
        $pl->addItem($playlistItem);

        $this->em->persist($playlistItem);
        $this->em->flush();

        return new Response('', 204);
    }

    private function removeFromPlaylist(Playlist $pl, int $index): Response
    {
        $item = $this->pir->findOneBy([
            'sort' => $index,
            'playlist' => $pl,
        ]);

        if (!$item) {
            throw $this->createNotFoundException('The index does not exist in this playlist');
        }

        $pl->removeItem($item);
        $this->em->remove($item);
        $this->em->flush();

        return new Response('', 204);
    }

    private function updatePlaylist(Playlist $pl, Request $req): Response
    {
        $name = $req->request->get('name');
        $rokuCategorySetting = $req->request->get('rokuCategorySetting');
        $sortMap = $req->request->all('sortMap');

        if ($sortMap) {
            return $this->reorderPlaylist($pl, $sortMap);
        } elseif (!$name && !$rokuCategorySetting) {
            throw new BadRequestException('prop change must be specified, or sortMap must be specified');
        }

        if ($name) {
            $pl->setName($name);
        }

        if ($rokuCategorySetting) {
            $pl->setRokuCategorySetting($rokuCategorySetting);
        }

        $errors = $this->vi->validate($pl);

        if (count($errors) > 0) {
            throw new ValidationFailedException('', errors);
        }

        $this->em->flush();
        return new Response('', 204);
    }

    private function reorderPlaylist(Playlist $pl, array $sortMap): Response
    {
        try {
            SortService::applySortMapToCollection($sortMap, $pl->getItems());
        } catch (Exception $e) {
            throw new BadRequestException($e->getMessage());
        }

        $this->em->flush();
        return new Response('', 204);
    }
}
