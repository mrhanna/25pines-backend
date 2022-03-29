<?php

namespace App\API\Entity;

use App\API\Repository\PlaylistItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlaylistItemRepository::class)]
class PlaylistItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $sort;

    #[ORM\ManyToOne(targetEntity: Playlist::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private $playlist;

    #[ORM\ManyToOne(targetEntity: AbstractContent::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $content;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSort(): ?int
    {
        return $this->sort;
    }

    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    public function getPlaylist(): ?Playlist
    {
        return $this->playlist;
    }

    public function setPlaylist(?Playlist $playlist): self
    {
        $this->playlist = $playlist;

        return $this;
    }

    public function getContent(): ?AbstractContent
    {
        return $this->content;
    }

    public function setContent(?AbstractContent $content): self
    {
        $this->content = $content;

        return $this;
    }
}
