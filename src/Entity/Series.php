<?php

namespace App\Entity;

use App\Repository\SeriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeriesRepository::class)]
class Series extends AbstractContent
{
    #[ORM\OneToMany(mappedBy: 'series', targetEntity: Episode::class)]
    private $episodes;

    public function __construct()
    {
        parent::__construct();
        $this->episodes = new ArrayCollection();
        $this->mediaType = 'series';
    }

    /**
     * @return Collection<int, Episode>
     */
    public function getEpisodes(): Collection
    {
        return $this->episodes;
    }

    public function addEpisode(Episode $episode): self
    {
        if (!$this->episodes->contains($episode)) {
            $this->episodes[] = $episode;
            $episode->setSeries($this);
        }

        return $this;
    }

    public function removeEpisode(Episode $episode): self
    {
        if ($this->episodes->removeElement($episode)) {
            // set the owning side to null (unless already changed)
            if ($episode->getSeries() === $this) {
                $episode->setSeries(null);
            }
        }

        return $this;
    }

    // API Helper Methods

    public function toArray(): ?array
    {
        $toReturn = parent::toArray();

        if ($this->episodes) {
            $toReturn['episodes'] = array_map(
                fn(Episode $episode) => $episode->toConciseArray(),
                $this->episodes->toArray()
            );
        }

        return $toReturn;
    }
}
