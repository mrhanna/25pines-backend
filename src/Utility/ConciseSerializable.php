<?php

namespace App\Utility;

interface ConciseSerializable extends \JsonSerializable
{
    public function conciseSerialize();
}
