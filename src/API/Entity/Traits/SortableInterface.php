<?php

namespace App\API\Entity\Traits;

interface SortableInterface
{
    public function getSort(): ?int;
    public function setSort(int $sort): self;
}
