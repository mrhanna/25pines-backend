<?php

namespace App\API\Entity;

use App\API\Repository\PlaylistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PlaylistRepository::class)]
class Playlist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'uuid')]
    private $uuid;

    #[ORM\Column(type: 'string', length: 30)]
    private $name;

    #[ORM\OneToMany(mappedBy: 'playlist', targetEntity: PlaylistItem::class, orphanRemoval: true)]
    private $items;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, PlaylistItem>
     */
    public function getItems(): Collection
    {
        $criteria = Criteria::create()
            ->orderBy(['sort' => 'ASC']);
        return $this->items->matching($criteria);
    }

    public function addItem(PlaylistItem $item): self
    {
        $this->insertItem($item, -1);

        return $this;
    }

    public function insertItem(PlaylistItem $item, int $index = -1): self
    {
        if (!$this->items->contains($item)) {
            $numItems = $this->items->count();

            // if index is valid
            if ($index >= -1 && $index <= $numItems) {
                $this->items[] = $item;
                $item->setPlaylist($this);

                // append or insert
                if ($index === -1 || $index === $numItems) {
                    $item->setSort($numItems);
                } else {
                    $item->setSort($index);

                    // increment sort for higher sorted items
                    $higherSortCriteria = Criteria::create()
                        ->andWhere(new Comparison('sort', '>', $index));

                    foreach ($this->items->matching($higherSortCriteria) as $item) {
                        $item->setSort($item->getSort + 1);
                    }
                }
            } else {
                // index was invalid
                throw new \InvalidArgumentException('index out of bounds');
            }
        }


        return $this;
    }

    public function removeItem(PlaylistItem $item): self
    {
        if ($this->items->removeElement($item)) {
            $index = $item->getSort();

            // decrement sort for higher sorted items
            $higherSortCriteria = Criteria::create()
                ->andWhere(new Comparison('sort', '>', $index));

            foreach ($this->items->matching($higherSortCriteria) as $item) {
                $item->setSort($item->getSort - 1);
            }
            // set the owning side to null (unless already changed)
            if ($item->getPlaylist() === $this) {
                $item->setPlaylist(null);
            }
        }

        return $this;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }
}
