<?php

namespace App\Utility;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

interface HalStrategy {
    public static function full();
    public static function concise();
}
