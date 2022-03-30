<?php

namespace App\API\Entity;

use App\API\Repository\AbstractContentRepository;
use App\API\Utility\ConciseSerializable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AbstractContentRepository::class)]
#[ORM\InheritanceType("SINGLE_TABLE")]
#[ORM\DiscriminatorColumn(name: "discr", type: "string")]
abstract class AbstractContent implements ConciseSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'uuid', unique: true)]
    protected $uuid;

    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\NotBlank]
    protected $title;

    // #[ORM\Column(type: 'string', length: 100, nullable: true)]
    // protected $thumbnail;
    #[ORM\ManyToOne(targetEntity: Thumbnail::class)]
    private $thumbnail;

    #[ORM\Column(type: 'date', nullable: true)]
    protected $releaseDate;

    #[ORM\Column(type: 'string', length: 200, nullable: true)]
    #[Assert\NotBlank]
    protected $shortDescription;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    #[Assert\NotBlank]
    protected $longDescription;

    #[ORM\ManyToMany(targetEntity: Tag::class, mappedBy: 'content')]
    protected $tags;

    #[ORM\Column(type: 'datetimetz', nullable: true)]
    protected $dateAdded;

    #[ORM\Column(type: 'simple_array', nullable: true)]
    protected $genres = [];

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->uuid = Uuid::v4();
        $this->dateAdded = new \DateTimeImmutable();
        $this->releaseDate = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getThumbnail(): ?Thumbnail
    {
        return $this->thumbnail;
    }

    public function setThumbnail(?Thumbnail $thumbnail): self
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    // public function getThumbnail(): ?string
    // {
    //     return $this->thumbnail;
    // }
    //
    // public function setThumbnail(string $thumbnail): self
    // {
    //     $this->thumbnail = $thumbnail;
    //
    //     return $this;
    // }

    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(string|\DateTimeInterface $releaseDate): self
    {
        if (is_string($releaseDate)) {
            $this->releaseDate = new \DateTimeImmutable($releaseDate);
        } elseif ($releaseDate instanceof \DateTimeInterface) {
            $this->releaseDate = $releaseDate;
        }

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

    public function setDateAdded(string|\DateTimeInterface $dateAdded): self
    {
        if (is_string($dateAdded)) {
            $this->dateAdded = new \DateTimeImmutable($dateAdded);
        } elseif ($dateAdded instanceof \DateTimeInterface) {
            $this->dateAdded = $dateAdded;
        }

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getGenres(): ?array
    {
        return $this->genres;
    }

    public function setGenres(?array $genres): self
    {
        $this->genres = $genres;

        return $this;
    }

    abstract public function getMediaType(): string;

    // API Helper Methods
    /**
     * @return array<string, mixed>
     */
    public function conciseSerialize(): mixed
    {
        return [
            'uuid' => $this->uuid,
            'mediaType' => $this->getMediaType(),
            'title' => $this->title,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): mixed
    {
        return array_merge($this->conciseSerialize(), [
            'shortDescription' => $this->shortDescription,
            'longDescription' => $this->longDescription,
            'releaseDate' => $this->releaseDate->format(\DateTimeInterface::ISO8601),
            'genres' => $this->genres,
            //'thumbnail' => $this->thumbnail,
            'dateAdded' => $this->dateAdded->format(\DateTimeInterface::ISO8601),
            //'tags' => $tags,
        ]);
    }

    public function setByArray(array $args): self
    {
        $settable = [
            'title',
            // 'thumbnail',
            'releaseDate',
            'shortDescription',
            'longDescription',
            'dateAdded',
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
