<?php

namespace App\API\Entity;

use App\API\Repository\EpisodeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EpisodeRepository::class)]
class Episode extends AbstractStreamableContent
{
    #[ORM\Column(type: 'smallint', nullable: true)]
    private $seasonNumber;

    #[ORM\Column(type: 'smallint', nullable: true)]
    #[Assert\NotBlank]
    private $episodeNumber;

    #[ORM\ManyToOne(targetEntity: Series::class, inversedBy: 'episodes')]
    #[Assert\NotNull]
    private $series;

    public function __construct()
    {
        parent::__construct();
    }

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

    public function getMediaType(): string
    {
        return 'episode';
    }

    //API Helper Methods
    /**
     * @return array<string, mixed>
     */
    public function conciseSerialize(): mixed
    {
        $toReturn = parent::conciseSerialize();

        $toReturn['episodeNumber'] = $this->episodeNumber;
        if (!is_null($this->seasonNumber)) {
            $toReturn['seasonNumber'] = $this->seasonNumber;
        }

        return $toReturn;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): mixed
    {
        $toReturn = parent::jsonSerialize();

        $toReturn['episodeNumber'] = $this->episodeNumber;
        if (!is_null($this->seasonNumber)) {
            $toReturn['seasonNumber'] = $this->seasonNumber;
        }

        /*
        if ($this->series)
            $toReturn['series'] = $this->series->toConciseArray();
        */

        return $toReturn;
    }

    public function setByArray(array $args): self
    {
        parent::setByArray($args);

        $settable = [
            'seasonNumber',
            'episodeNumber',
        ];

        foreach ($settable as $var) {
            if (isset($args[$var])) {
                $func = 'set' . ucfirst($var);
                $this->$func($args[$var]);
            }
        }

        return $this;
    }
}
