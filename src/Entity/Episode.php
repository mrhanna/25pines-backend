<?php

namespace App\Entity;

use App\Repository\EpisodeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EpisodeRepository::class)]
class Episode extends StreamableContent
{
    #[ORM\Column(type: 'smallint', nullable: true)]
    private $seasonNumber;

    #[ORM\Column(type: 'smallint', nullable: true)]
    private $episodeNumber;

    #[ORM\ManyToOne(targetEntity: Series::class, inversedBy: 'episodes')]
    private $series;

    public function getSeasonNumber(): ?int
    {
        return $this->seasonNumber;
    }

    public function setSeasonNumber(?int $seasonNumber): self
    {
        $this->seasonNumber = $seasonNumber;

        return $this;
    }

    public function getEpisodeNumber(): ?int
    {
        return $this->episodeNumber;
    }

    public function setEpisodeNumber(?int $episodeNumber): self
    {
        $this->episodeNumber = $episodeNumber;

        return $this;
    }

    public function getSeries(): ?Series
    {
        return $this->series;
    }

    public function setSeries(?Series $series): self
    {
        $this->series = $series;

        return $this;
    }

    //API Helper Methods

    public function toConciseArray(): ?array
    {
        $toReturn = array_merge(parent::toConciseArray(), [
            'seasonNumber' => $this->seasonNumber,
            'episodeNumber' => $this->episodeNumber
        ]);

        return $toReturn;
    }

    public function toArray(): ?array
    {
        $toReturn = array_merge(parent::toArray(), [
            'seasonNumber' => $this->seasonNumber,
            'episodeNumber' => $this->episodeNumber
        ]);

        if ($this->series)
            $toReturn['series'] = $this->series->toConciseArray();

        return $toReturn;
    }
}
