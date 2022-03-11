<?php

namespace App\Entity;

use App\Repository\AbstractContentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AbstractContentRepository::class)]
#[ORM\InheritanceType("SINGLE_TABLE")]
#[ORM\DiscriminatorColumn(name: "discr", type: "string")]
abstract class AbstractContent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'uuid', unique: true)]
    protected $uuid;

    #[ORM\Column(type: 'string', length: 50)]
    protected $title;

    #[ORM\Column(type: 'string', length: 100)]
    protected $thumbnail;

    #[ORM\Column(type: 'date', nullable: true)]
    protected $releaseDate;

    #[ORM\Column(type: 'string', length: 200, nullable: true)]
    protected $shortDescription;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    protected $longDescription;

    #[ORM\ManyToMany(targetEntity: Tag::class, mappedBy: 'content')]
    protected $tags;

    #[ORM\Column(type: 'datetimetz', nullable: true)]
    protected $dateAdded;

    #[ORM\Column(type: 'simple_array', nullable: true)]
    protected $genres = [];

    #[ORM\Column(type: 'string', length: 14)]
    protected $mediaType;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->uuid = Uuid::v4();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function setUuid($uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(string $thumbnail): self
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->releaseDate;
    }

    public function setReleaseDate($releaseDate): self
    {
        if (is_string($releaseDate))
            $this->releaseDate = new \DateTimeImmutable($releaseDate);
        else if ($releaseDate instanceof \DateTimeInterface)
            $this->releaseDate = $releaseDate;

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(?string $shortDescription): self
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getLongDescription(): ?string
    {
        return $this->longDescription;
    }

    public function setLongDescription(?string $longDescription): self
    {
        $this->longDescription = $longDescription;

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
            $tag->addContent($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->removeElement($tag)) {
            $tag->removeContent($this);
        }

        return $this;
    }

    public function getDateAdded(): ?\DateTimeInterface
    {
        return $this->dateAdded;
    }

    public function setDateAdded($dateAdded): self
    {
        if (is_string($dateAdded))
            $this->dateAdded = new \DateTimeImmutable($dateAdded);
        else if ($dateAdded instanceof \DateTimeInterface)
            $this->dateAdded = $dateAdded;

        return $this;
    }

    public function getGenres(): ?array
    {
        return $this->genres;
    }

    public function setGenres(?array $genres): self
    {
        $this->genres = $genres;

        return $this;
    }

    public function getMediaType(): ?string
    {
        return $this->mediaType;
    }

    public function setMediaType(string $mediaType): self
    {
        $this->mediaType = $mediaType;

        return $this;
    }

    // API Helper Methods
    public function toConciseArray(): ?array
    {
        $toReturn = array(
            'uuid' => $this->uuid,
            'mediaType' => $this->mediaType,
            'title' => $this->title
        );

        return $toReturn;
    }

    public function toArray(): ?array
    {
        $tags = array_map(fn(Tag $tag) => $tag->getName(), $this->tags->toArray());

        $toReturn = array_merge($this->toConciseArray(), array(
            'shortDescription' => $this->shortDescription,
            'longDescription' => $this->longDescription,
            'releaseDate' => $this->releaseDate->format(\DateTimeInterface::ISO8601),
            'genres' => $this->genres,
            'thumbnail' => $this->thumbnail,
            'dateAdded' => $this->dateAdded->format(\DateTimeInterface::ISO8601),
            'tags' => $tags,
        ));

        return $toReturn;
    }

    public function setByArray(array $args): self
    {
        $settable = [
            'title',
            'thumbnail',
            'releaseDate',
            'shortDescription',
            'longDescription',
            'dateAdded'
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
