<?php

namespace App\API\Entity;

use App\API\Repository\SeriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SeriesRepository::class)]
class Series extends AbstractContent
{
    #[ORM\OneToMany(mappedBy: 'series', targetEntity: Episode::class)]
    private $episodes;

    public function __construct()
    {
        parent::__construct();
        $this->episodes = new ArrayCollection();
    }

    /**
     * @return Collection<int, Episode>
     */
    public function getEpisodes(): Collection
    {
        $criteria = Criteria::create()
            ->orderBy(['seasonNumber' => 'ASC', 'episodeNumber' => 'ASC']);
        return $this->episodes->matching($criteria);
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

    public function episodeCount(): ?int
    {
        return $this->episodes->count();
    }

    public function getMediaType(): string
    {
        return 'series';
    }

    // API Helper Methods

    public function conciseSerialize(): ?array
    {
        return array_merge(
            parent::conciseSerialize(),
            ['episodeCount' => $this->episodeCount()]
        );
    }

    public function jsonSerialize(): ?array
    {
        return array_merge(
            parent::jsonSerialize(),
            ['episodeCount' => $this->episodeCount()]
        );

        /*
        $toReturn['episodes'] = array_map(
            fn(Episode $episode) => $episode->toConciseArray(),
            $this->episodes->toArray()
        );*/
    }
}
