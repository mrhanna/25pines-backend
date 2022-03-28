<?php

namespace App\Image;

use App\API\Repository\ThumbnailRepository;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImageGenerator extends AbstractController
{
    public const SUPPORTED_DIMENSIONS = [
        [800, 450],
        [400, 225],
    ];

    /*
     * This route should only be called when an image doesn't exist in cache.
     */
    #[Route('/images/cached/{width}x{height}/{name}.jpg', 'generateImage', methods: ['GET'])]
    public function generateImage(ThumbnailRepository $tr, int $width, int $height, string $name): Response
    {
        if (!$this->areDimensionsSupported($width, $height)) {
            throw $this->createNotFoundException('Image not found. (dimension)');
        }

        $destDir = $this->getParameter('cachedImageDir') . $width . 'x' . $height;
        $destFile = $destDir . '/' . $name . '.jpg';

        if (!is_dir($destDir)) {
            mkdir($destDir);
        }

        // if (!file_exists($sourceFile)) {
        //     throw $this->createNotFoundException('Image not found. (source) ' . $sourceFile);
        // }

        $imageEntity = $tr->findOneBy(['name' => $name]);

        if (!$imageEntity) {
            throw $this->createNotFoundException('Image not found. (source)');
        }

        $data = stream_get_contents($imageEntity->getData());

        $imagine = new Imagine();
        $image = $imagine->load($data);
        $image->resize(new Box($width, $height))
            ->save($destFile);

        return new Response(
            $image->get('jpg'),
            200,
            [
                'Content-Type' => 'image/jpeg',
            ]
        );
    }

    public function toUrl(
        string $name,
        int $width = self::SUPPORTED_DIMENSIONS[0][0],
        int $height = self::SUPPORTED_DIMENSIONS[0][1]
    ): string {
        return $this->generateUrl('generateImage', [
            'width' => $width,
            'height' => $height,
            'name' => $name,
        ]);
    }

    public function toJsonArray(string $name): mixed
    {
        return array_map(
            fn(array $dims) => [
                'width' => $dims[0],
                'height' => $dims[1],
                'url' => $this->toUrl($name, $dims[0], $dims[1]),
            ],
            self::SUPPORTED_DIMENSIONS
        );
    }

    /**
     * @return array<string>
     */
    public function toArray(string $name): array
    {
        return array_map(
            fn(array $dims) => $this->toUrl($name, $dims[0], $dims[1]),
            self::SUPPORTED_DIMENSIONS
        );
    }

    public function areDimensionsSupported(int $width, int $height): bool
    {
        return in_array([$width, $height], self::SUPPORTED_DIMENSIONS);
    }
}
