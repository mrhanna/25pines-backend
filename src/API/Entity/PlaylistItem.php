<?php

namespace App\API\Entity;

use App\API\Entity\Traits\SortableInterface;
use App\API\Entity\Traits\SortableTrait;
use App\API\Repository\PlaylistItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlaylistItemRepository::class)]
class PlaylistItem implements SortableInterface
{
    use SortableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

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
