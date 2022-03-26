<?php

namespace App\API\Utility;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUploadService
{
    protected string $uploadDir;

    public function __construct(string $uploadDir)
    {
        $this->uploadDir = $uploadDir;
    }

    public function uploadImage(UploadedFile $image): string
    {
        if (!$image->isValid()) {
            throw new \Exception('file is invalid');
        }

        // TODO: validate the image!

        $safeFileName = substr(str_shuffle(MD5(microtime())), 0, 10);
        $image->move(
            $this->uploadDir,
            $safeFileName . '.jpg'
        );

        return $safeFileName;
    }
}
