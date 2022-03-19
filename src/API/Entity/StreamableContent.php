<?php

namespace App\API\Entity;
use App\API\Entity\AbstractStreamableContent;
use App\API\Repository\StreamableContentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StreamableContentRepository::class)]
class StreamableContent extends AbstractStreamableContent
{
    #[ORM\Column(type: 'string', length: 14)]
    #[Assert\NotBlank]
    #[Assert\Choice(['shortFormVideo', 'tvSpecial', 'movie'])]
    protected $mediaType;

    public function __construct()
    {
        parent::__construct();
    }

    public function getMediaType(): ?string
    {
        return $this->mediaType;
    }

    public function setMediaType(string $mediaType): self
    {
        $this->mediaType = $mediaType;

        return $this;
    }
}
