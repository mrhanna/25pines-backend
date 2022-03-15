<?php

namespace App\Entity;
use App\Entity\AbstractStreamableContent;
use App\Repository\StreamableContentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StreamableContentRepository::class)]
class StreamableContent extends AbstractStreamableContent
{
    public function __construct()
    {
        parent::__construct();
    }
}
