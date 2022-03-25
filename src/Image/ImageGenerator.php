<?php

namespace App\Image;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImageGenerator extends AbstractController
{
    public const SUPPORTED_DIMENSIONS = [
        [800, 450],
    ];
    /*
     * This route should only be called when an image doesn't exist in cache.
     */
    #[Route('/images/cached/{width}x{height}/{name}.jpg', 'generateImage', methods: ['GET'])]
    public function generateImage(int $width, int $height, string $name): Response
    {
        if (!$this->areDimensionsSupported($width, $height)) {
            throw $this->createNotFoundException('Image not found. (dimension)');
        }

        $sourceFile = __DIR__ . '/../../uploads/images/' . $name . '.jpg';
        $destFile = __DIR__ . '/../../public/images/cached/' . $width . 'x' . $height . '/' . $name . '.jpg';

        if (!file_exists($sourceFile)) {
            throw $this->createNotFoundException('Image not found. (source)');
        }

        $imagine = new Imagine();

        $image = $imagine->open($sourceFile);
        $image->resize(new Box($width, $height))
            ->save($destFile);

        return new Response(
            $image->get('jpg'),
            200,
            [
                'Content-Type' => 'image/jpg',
            ]
        );
    }

    public function areDimensionsSupported(int $width, int $height): bool
    {
        return in_array([$width, $height], self::SUPPORTED_DIMENSIONS);
    }
}
