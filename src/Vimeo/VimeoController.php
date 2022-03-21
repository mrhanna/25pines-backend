<?php

namespace App\Vimeo;

use Curl\Curl;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VimeoController extends AbstractController
{
    const QUALITY_MAP = [
        '1080p'     => 'FHD',
        '240p'      => 'SD',
        '2k'        => 'FHD',
        '360p'      => 'SD',
        '480p'      => 'SD',
        '4k'        => 'UHD',
        '540p'      => 'SD',
        '5k'        => 'UHD',
        '6k'        => 'UHD',
        '720p'      => 'HD',
        '7k'        => 'UHD',
        '8k'        => 'UHD',
    ];

    private $clientId;
    private $clientSecret;

    public function __construct()
    {
        $this->clientId = $this->getParameter('app.vimeo.clientId');
        $this->clientSecret = $this->getParameter('app.vimeo.clientSecret');
    }
    #[Route('/fetch/{id}', 'vimeoFetch')]
    public function fetch(string $id): Response
    {
        //$token = $req->headers->get('VimeoAuth');
        $token = '140ba6aeeb09d2c1a8d8826fac779ddd';
        if (!$token) {
            return $this->redirectToRoute('vimeoLogin', [], 302);
        }

        $curl = new Curl();
        $curl->setHeader('Authorization', 'bearer ' . $token);
        $curl->get('https://api.vimeo.com/videos/' . $id);

        if ($curl->error) {
            return $this->json($curl->error_code);
        }

        $content = json_decode($curl->response, true);

        return $this->json([
            'title' => $content['name'],
            'longDescription' => $content['description'],
            'releaseDate' => $content['release_time'], //ISO8601
            'thumbnail' => $content['pictures']['base_link'],
            'dateAdded' => $content['created_time'],
            'duration' => $content['duration'],
            'language' => $content['language'] ?? 'en-US',
            'tags' => array_map(fn(array $tag) => $tag['name'], $content['tags']),
            'videos' => array_map(self::class . '::toNativeVideo', $content['files']),
        ]);
    }

    /**
     * @return array<string, string>
    */
    public function toNativeVideo(array $video): ?array
    {
        $url = $video['link'];
        $quality = self::QUALITY_MAP[$video['rendition']] ?? null;
        $type = 'MP4';

        if (!$quality) {
            if ($video['rendition'] === 'adaptive') {
                $type = 'HLS';
                $quality = 'FHD';
            } else {
                return null;
            }
        }

        if ($video['type'] !== 'video/mp4') {
            return null;
        }

        return [
            'url' => $url,
            'quality' => $quality,
            'type' => $type,
            'rendition' => $video['rendition'],
        ];
    }

    #[Route('/login', 'vimeoLogin')]
    public function login(Request $req): Response
    {
        /*if ($req->hasPreviousSession()) {
            $session = $req->getSession();
            $token = $session->get('vimeoToken');
            if ($token)
                return $this->json($token);
        }*/

        $code = $req->query->get('code');
        $state = $req->query->get('state');
        $redirectUri = $this->generateUrl('vimeoLogin', [], 0);

        if (!$code || !$state) {
            $authUrl = sprintf(
                'https://api.vimeo.com/oauth/authorize?response_type=code&client_id=%s&redirect_uri=%s&state=%s&scope=%s',
                $this->clientId,
                $redirectUri,
                'asodfjoiasdjfoi',
                'public private video_files'
            );

            return $this->redirect($authUrl, 302);
        }


        $curl = new Curl();
        $curl->setHeader('Authorization', 'basic ' . base64_encode($this->clientId . ':' . $this->clientSecret));
        $curl->setHeader('Content-Type', 'application/json');
        $curl->setHeader('Accept', 'application/vnd.vimeo.*+json;version=3.4');
        $curl->post(
            'https://api.vimeo.com/oauth/access_token',
            json_encode([
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $redirectUri,
            ])
        );

        if ($curl->error) {
            return $this->json($curl->response);
        }

        $res = json_decode($curl->response, true);

        return [
            'access_token' => $res['access_token'],
            'name' => $res['user']['name'],
            'scope' => $res['scope'],
        ];
    }
}
