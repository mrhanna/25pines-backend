<?php

namespace App\API\Utility;

interface HalStrategy
{
    public static function full(): ?callable;
    public static function concise(): ?callable;
}
