<?php

namespace App\API\Controller;

use App\API\Entity\Playlist;
use App\API\Entity\PlaylistItem;
use App\API\Repository\AbstractContentRepository;
use App\API\Repository\PlaylistRepository;
use App\API\Repository\PlaylistItemRepository;
use App\API\Utility\HalJson;
use App\API\Utility\HalJsonFactory;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Routing\Annotation\Route;

class PlaylistController extends AbstractController
{
    private $em;
    private AbstractContentRepository $acr;
    private HalJsonFactory $hjf;
    private PlaylistRepository $pr;
    private PlaylistItemRepository $pir;

    public function __construct(
        AbstractContentRepository $acr,
        HalJsonFactory $hjf,
        ManagerRegistry $doctrine,
        PlaylistRepository $pr,
        PlaylistItemRepository $pir
    ) {
        $this->acr = $acr;
        $this->em = $doctrine->getManager();
        $this->hjf = $hjf;
        $this->pr = $pr;
        $this->pir = $pir;
    }

    #[Route('/playlists', name: 'showPlaylists', methods: ['GET', 'POST'])]
    public function showPlaylists(Request $req): Response
    {
        switch ($req->getMethod()) {
            case 'GET':
                return $this->readAllPlaylists();
                break;
            case 'POST':
                $name = $req->request->get('name');

                if (!$name) {
                    throw new BadRequestException('name must be specified');
                }

                return $this->createPlaylist($name);
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
                $name = $req->request->get('name');
                // $from = $req->request->get('from');
                // $to = $req->request->get('to');
                $sortMap = $req->request->all('sortMap');

                if ($name) {
                    $playlist->setName($name);
                    $this->em->flush();
                    return new Response('', 204);
                // } elseif (is_int($from) && is_int($to) && $from !== $to) {
                //     return $this->movePlaylistItem($playlist, $from, $to);
                } elseif ($sortMap) {
                    return $this->reorderPlaylist($playlist, $sortMap);
                }

                throw new BadRequestException('name must be specified, or sortMap must be specified');
                break;
            case 'DELETE':
                return $this->deletePlaylist($playlist);
        }
    }

    public function readAllPlaylists(): Response
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

    public function createPlaylist(string $name): Response
    {
        $pl = new Playlist();
        $pl->setName($name);

        $this->em->persist($pl);
        $this->em->flush();

        return $this->redirectToRoute(
            'showPlaylist',
            ['uuid' => $pl->getUuid()],
            201
        );
    }

    public function readPlaylist(Playlist $pl): Response
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
            ->link('self', $this->generateUrl(
                'showPlaylist',
                ['uuid' => $pl->getUuid()],
                0
            ))
            ->link('collection', $this->generateUrl('showPlaylists', [], 0));

        return $this->json($playlistJson);
    }

    public function deletePlaylist(Playlist $pl): Response
    {
        $this->em->remove($pl);
        $this->em->flush();

        return new Response('', 204);
    }

    public function addToPlaylist(Playlist $pl, string $contentUuid): Response
    {
        return $this->insertIntoPlaylist($pl, $contentUuid, -1);
    }

    public function insertIntoPlaylist(Playlist $pl, string $contentUuid, int $index = -1): Response
    {
        $content = $this->acr->findOneBy(['uuid' => $contentUuid]);

        if (!$content) {
            throw $this->createNotFoundException('The content could not be found');
        }

        $playlistItem = new PlaylistItem();
        $playlistItem->setContent($content);

        try {
            $pl->insertItem($playlistItem, $index);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestException($e->getMessage());
        }

        $this->em->persist($playlistItem);
        $this->em->flush();

        return new Response('', 204);
    }

    public function removeFromPlaylist(Playlist $pl, int $index): Response
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

    // public function movePlaylistItem(Playlist $pl, int $from, int $to): Response
    // {
    //     $item = $this->pir->findOneBy(['sort' => $from]);
    //
    //     if (!$item) {
    //         throw $this->createNotFoundException('The index does not exist in this playlist');
    //     }
    //
    //     $pl->removeItem($item);
    //     $pl->insertItem($item, $to);
    //
    //     $this->em->flush();
    //     return new Response('', 204);
    // }

    public function reorderPlaylist(Playlist $pl, array $sortMap): Response
    {
        $count = count($sortMap);
        // validate the map
        if ($count !== count($pl->getItems())) {
            throw new BadRequestException('sortMap length does not match item count');
        }

        $has = array_fill(0, $count, false);

        foreach ($sortMap as $index) {
            if ($index >= $count || $index < 0) {
                throw new BadRequestException('The index ' . $index . ' is out of bounds');
            }

            if ($has[$index]) {
                throw new BadRequestException('The index ' . $index . ' was reused.');
            }

            $has[$index] = true;
        }

        for ($i = 0; $i < $count; $i++) {
            $pl->getItems()->get($i)->setSort($sortMap[$i]);
        }

        $this->em->flush();
        return new Response('', 204);
    }
}
