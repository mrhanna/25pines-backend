<?php

namespace App\API\Utility;

interface ConciseSerializable extends \JsonSerializable
{
    public function conciseSerialize();
}
