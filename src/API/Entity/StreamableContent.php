<?php

namespace App\API\Entity;
use App\API\Entity\AbstractStreamableContent;
use App\API\Repository\StreamableContentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StreamableContentRepository::class)]
class StreamableContent extends AbstractStreamableContent
{
    public function __construct()
    {
        parent::__construct();
    }
}
