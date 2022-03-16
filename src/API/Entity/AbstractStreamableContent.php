<?php

namespace App\API\Entity;

use App\API\Repository\AbstractStreamableContentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AbstractStreamableContentRepository::class)]
class AbstractStreamableContent extends AbstractContent
{
    #[ORM\OneToMany(mappedBy: 'content', targetEntity: Video::class)]
    protected $videos;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\NotNull]
    #[Assert\Positive]
    protected $duration;

    #[ORM\Column(type: 'string', length: 5, nullable: true)]
    #[Assert\NotBlank]
    protected $language;

    public function __construct()
    {
        parent::__construct();
        $this->videos = new ArrayCollection();
        $this->language = 'en-US';
    }

    /**
     * @return Collection<int, Video>
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function addVideo(Video $video): self
    {
        if (!$this->videos->contains($video)) {
            $this->videos[] = $video;
            $video->setContent($this);
        }

        return $this;
    }

    public function removeVideo(Video $video): self
    {
        if ($this->videos->removeElement($video)) {
            // set the owning side to null (unless already changed)
            if ($video->getContent() === $this) {
                $video->setContent(null);
            }
        }

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): self
    {
        $this->language = $language;

        return $this;
    }

    // API Helper Methods
    public function jsonSerialize(): ?array
    {
        //$videos = array_map(fn(Video $video) => $video->toArray(), $this->videos->toArray());

        $toReturn = array_merge(parent::jsonSerialize(), [
            'duration' => $this->duration,
            'language' => $this->language,
            //'videos' => $videos
        ]);

        return $toReturn;
    }

    public function setByArray(array $args): self
    {
        parent::setByArray($args);

        $settable = [
            'duration',
            'language'
        ];

        foreach ($settable as $var) {
            if (isset($args[$var])) {
                $func = 'set'.ucfirst($var);
                $this->$func($args[$var]);
            }
        }

        return $this;
    }
}
