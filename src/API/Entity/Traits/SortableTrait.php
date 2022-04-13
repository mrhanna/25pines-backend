<?php

namespace App\API\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait SortableTrait
{
    #[ORM\Column(type: 'integer')]
    private $sort;

    public function getSort(): ?int
    {
        return $this->sort;
    }

    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }
}
