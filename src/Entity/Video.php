<?php

namespace App\Entity;

use App\Repository\VideoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VideoRepository::class)]
class Video implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 100)]
    private $url;

    #[ORM\Column(type: 'string', length: 3)]
    private $quality;

    #[ORM\Column(type: 'string', length: 6)]
    private $videoType;

    #[ORM\ManyToOne(targetEntity: StreamableContent::class, inversedBy: 'videos')]
    private $content;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getQuality(): ?string
    {
        return $this->quality;
    }

    public function setQuality(string $quality): self
    {
        $this->quality = $quality;

        return $this;
    }

    public function getVideoType(): ?string
    {
        return $this->videoType;
    }

    public function setVideoType(string $videoType): self
    {
        $this->videoType = $videoType;

        return $this;
    }

    public function getContent(): ?StreamableContent
    {
        return $this->content;
    }

    public function setContent(?StreamableContent $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function jsonSerialize(): ?array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'quality' => $this->quality,
            'videoType' => $this->videoType
        ];
    }

}
