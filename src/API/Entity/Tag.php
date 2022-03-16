<?php

namespace App\API\Entity;

use App\API\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TagRepository::class)]
class Tag implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 20, unique: true)]
    #[Assert\NotBlank]
    private $name;

    #[ORM\ManyToMany(targetEntity: AbstractContent::class, inversedBy: 'tags')]
    private $content;

    public function __construct()
    {
        $this->content = new ArrayCollection();
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
     * @return Collection<int, AbstractContent>
     */
    public function getContent(): Collection
    {
        return $this->content;
    }

    public function addContent(AbstractContent $content): self
    {
        if (!$this->content->contains($content)) {
            $this->content[] = $content;
        }

        return $this;
    }

    public function removeContent(AbstractContent $content): self
    {
        $this->content->removeElement($content);

        return $this;
    }

    public function jsonSerialize() {
        return ['name' => $this->name];
    }
}
