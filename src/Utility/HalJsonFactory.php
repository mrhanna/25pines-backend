<?php

namespace App\Utility;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Utility\HalJsonFactory\AbstractContentStrategy;

class HalJsonFactory
{
    private $router;
    private $registry;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;

        // Strategy Registration
        $this->registry = [
            \App\Entity\AbstractContent::class => \App\Hal\AbstractContentStrategy::class,
            \App\Entity\Episode::class => \App\Hal\EpisodeStrategy::class,
            \App\Entity\Series::class => \App\Hal\SeriesStrategy::class,
            \App\Entity\StreamableContent::class => \App\Hal\StreamableContentStrategy::class,
        ];
    }

    public function create(\JsonSerializable $obj): HalJson
    {
        $hj = new HalJson($obj->jsonSerialize());

        foreach ($this->registry as $class => $strategy) {
            if ($obj instanceof $class) {
                $fn = $strategy::full()?->bindTo($this, $this);
                if (!is_null($fn)) $fn($hj, $obj);
            }
        }

        return $hj;
    }

    public function createConcise(ConciseSerializable $obj): HalJson
    {
        $hj = new HalJson($obj->conciseSerialize());

        foreach($this->registry as $class => $strategy) {
            if ($obj instanceof $class) {
                $fn = $strategy::concise()?->bindTo($this, $this);
                if (!is_null($fn)) $fn($hj, $obj);
            }
        }

        return $hj;
    }

    public function mapCreate(Iterable $objs): array
    {
        $return = [];
        foreach ($objs as $obj) {
            $return[] = $this->create($obj);
        }

        return $return;
    }

    public function mapCreateConcise(Iterable $objs): array
    {
        $return = [];
        foreach ($objs as $obj) {
            $return[] = $this->createConcise($obj);
        }

        return $return;
    }

    private function generateUrl(string $name, array $args): string
    {
        return $this->router->generate($name, $args, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
