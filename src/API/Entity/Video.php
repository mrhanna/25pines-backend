<?php

namespace App\API\Entity;

use App\API\Repository\VideoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VideoRepository::class)]
class Video implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\Url]
    #[Assert\NotBlank]
    private $url;

    #[ORM\Column(type: 'string', length: 3)]
    #[Assert\Choice(['SD', 'HD', 'FHD', 'UHD'])]
    private $quality;

    #[ORM\Column(type: 'string', length: 6)]
    #[Assert\Choice(['HLS', 'SMOOTH', 'DASH', 'MP4', 'MOV', 'M4V'])]
    private $videoType;

    #[ORM\ManyToOne(targetEntity: AbstractStreamableContent::class, inversedBy: 'videos')]
    #[Assert\NotNull]
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

    public function getContent(): ?AbstractStreamableContent
    {
        return $this->content;
    }

    public function setContent(?AbstractStreamableContent $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'quality' => $this->quality,
            'videoType' => $this->videoType,
        ];
    }
}
