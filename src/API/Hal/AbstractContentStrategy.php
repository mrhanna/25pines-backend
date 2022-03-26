<?php

namespace App\API\Hal;

use App\API\Utility\HalJson;
use App\API\Utility\HalStrategy;

class AbstractContentStrategy implements HalStrategy
{
    public static function full(): ?callable
    {
        return function (HalJson &$hj, $ac): void {
            $hj->link('self', $this->generateUrl('showContent', ['uuid' => $ac->getUuid()]));

            $thumbnail = $ac->getThumbnail();
            if (filter_var($thumbnail, FILTER_VALIDATE_URL)) {
                $hj->set('thumbnail', ['url' => $thumbnail]);
            } elseif ($thumbnail) {
                $hj->set('thumbnail', $this->ig->toJsonArray($thumbnail));
            }
        };
    }

    public static function concise(): ?callable
    {
        return self::full();
    }
}
