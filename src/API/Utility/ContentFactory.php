<?php

namespace App\API\Utility;

use App\API\Entity\AbstractContent;
use App\API\Entity\Episode;
use App\API\Entity\Series;
use App\API\Entity\StreamableContent;

class ContentFactory
{
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
    public function createFromArray(array $args, ?string $mediaType = null): AbstractContent
    {
        // if mediaType isn't explicitly given
        if (is_null($mediaType)) {
            // use mediaType from array
            if (isset($args['mediaType'])) {
                $mediaType = $args['mediaType'];
            } else {
                throw new \Exception(); // TODO: better error handling
            }
        }

        return $this->create($mediaType)->setByArray($args);
    }
}
