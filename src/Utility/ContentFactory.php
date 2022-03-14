<?php

namespace App\Utility;

use App\Entity\AbstractContent;
use App\Entity\Episode;
use App\Entity\Series;
use App\Entity\StreamableContent;

class ContentFactory {
    public function create(string $mediaType): AbstractContent
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
    public function createFromArray(array $args, string $mediaType = null): AbstractContent
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

        return $this->create($mediaType)->setByArray($args);
    }
}
