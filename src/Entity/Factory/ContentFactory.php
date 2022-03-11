<?php

namespace App\Entity\Factory;

use App\Entity\AbstractContent;
use App\Entity\Episode;
use App\Entity\Series;
use App\Entity\StreamableContent;
use App\Entity\Tag;
use App\Entity\Video;

class ContentFactory {
    public static function create(string $mediaType): AbstractContent
    {
        $toReturn = null;

        switch ($mediaType) {
            case 'episode':
                $toReturn = new Episode();
                break;
            case 'series':
                $toReturn = new Series();
                break;
            default:
                $toReturn = new StreamableContent();
                $toReturn->setMediaType($mediaType);
        }

        return $toReturn;
    }

    // Returns a child of AbstractContent, provided an associative array.
    // Optional $mediaType param can be used to help convert one content
    // to another type of content.
    public static function createFromArray(array $args, string $mediaType = null): AbstractContent
    {
        // if mediaType isn't explicitly given
        if (is_null($mediaType)) {
            // use mediaType from array
            if (isset($args['mediaType'])) {
                $mediaType = $args['mediaType'];
            }

            //TODO: better error handling.
            else throw new Exception();
        }

        return self::create($mediaType)->setByArray($args);
    }
}
