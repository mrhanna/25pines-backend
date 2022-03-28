<?php

namespace App\API\Entity;

use App\API\Repository\ThumbnailRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ThumbnailRepository::class)]
class Thumbnail
{
    public const HORIZONTAL = 'h';
    public const VERTICAL = 'v';
    public const SQUARE = 's';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: '10')]
    #[Assert\NotBlank]
    private string $name;

    #[ORM\Column(type: 'string', length: '1')]
    #[Assert\Choice(['h', 'v', 's'])]
    private string $layout;

    #[ORM\Column(type: 'blob')]
    private $data;

    public function __construct()
    {
        // generate random 10-char long file name
        $this->name = substr(str_shuffle(MD5(microtime())), 0, 10);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLayout(): string
    {
        return $this->layout;
    }

    public function setLayout(string $layout): self
    {
        $this->layout = $layout;
        return $this;
    }

    public function getData(): mixed // binary resource
    {
        return $this->data;
    }

    public function setData(mixed $data): self
    {
        $this->data = $data;
        return $this;
    }
}
