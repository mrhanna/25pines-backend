<?php

namespace App\API\Utility;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\API\Hal as Strategies;
use App\Image\ImageGenerator;

class HalJsonFactory
{
    private $router;
    private $ig;
    private $registry;

    public function __construct(UrlGeneratorInterface $router, ImageGenerator $ig)
    {
        $this->router = $router;
        $this->ig = $ig;

        // Strategy Registration
        $this->registry = [
            \App\API\Entity\Tag::class => Strategies\TagStrategy::class,
            \App\API\Entity\AbstractContent::class => Strategies\AbstractContentStrategy::class,
            \App\API\Entity\Episode::class => Strategies\EpisodeStrategy::class,
            \App\API\Entity\Series::class => Strategies\SeriesStrategy::class,
            \App\API\Entity\AbstractStreamableContent::class => Strategies\AbstractStreamableContentStrategy::class,
            \App\API\Entity\StreamableContentStrategy::class => Strategies\StreamableContentStrategy::class,
            \App\API\Entity\Video::class => Strategies\VideoStrategy::class,
        ];
    }

    public function create(\JsonSerializable $obj): HalJson
    {
        $hj = new HalJson($obj->jsonSerialize());

        foreach ($this->registry as $class => $strategy) {
            if ($obj instanceof $class) {
                $fn = $strategy::full()?->bindTo($this, $this);
                if (!is_null($fn)) {
                    $fn($hj, $obj);
                }
            }
        }

        return $hj;
    }

    public function createConcise(\JsonSerializable $obj): HalJson
    {
        $hj = new HalJson();

        if ($obj instanceof ConciseSerializable) {
            $hj->setArray($obj->conciseSerialize());
        } else {
            $hj->setArray($obj->jsonSerialize());
        }

        foreach ($this->registry as $class => $strategy) {
            if ($obj instanceof $class) {
                $fn = $strategy::concise()?->bindTo($this, $this);
                if (!is_null($fn)) {
                    $fn($hj, $obj);
                }
            }
        }

        return $hj;
    }

    /**
     * @return array<HalJson>
     */
    public function mapCreate(iterable $objs): array
    {
        $return = [];
        foreach ($objs as $obj) {
            $return[] = $this->create($obj);
        }

        return $return;
    }

    /**
     * @return array<HalJson>
     */
    public function mapCreateConcise(iterable $objs): array
    {
        $return = [];
        foreach ($objs as $obj) {
            $return[] = $this->createConcise($obj);
        }

        return $return;
    }

    public function createCollection(string $name, iterable $objs): HalJson
    {
        $hj = new HalJson();
        $hj->embedArray($name, $this->mapCreateConcise($objs));
        return $hj;
    }

    protected function generateUrl(string $name, array $args = []): string
    {
        return $this->router->generate($name, $args, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
