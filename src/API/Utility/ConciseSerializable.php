<?php

namespace App\API\Utility;

interface ConciseSerializable extends \JsonSerializable
{
    /**
     * @return array<string, mixed>
     */
    public function conciseSerialize(): mixed;
}
