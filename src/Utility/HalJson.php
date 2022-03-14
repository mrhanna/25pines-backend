<?php

namespace App\Utility;

class HalJson implements \JsonSerializable
{
    private $links;
    private $embedded;
    private $json;

    public function __construct($json = [])
    {
        $this->links = [];
        $this->embedded = [];
        $this->json = [];
        $this->setArray($json);
    }

    public function link(string $name, string $uri): self
    {
        $this->links[$name] = $uri;
        return $this;
    }

    public function embed(string $name, HalJson $json): self
    {
        $this->embedded[$name] = $json;
        return $this;
    }

    public function embedPush(string $name, HalJson $json): self
    {
        $this->embedded[$name][] = $json;
        return $this;
    }

    public function embedArray(string $name, array $jsons): self
    {
        $this->embedded[$name] = $jsons;
        return $this;
    }

    public function set(string $key, $val): self
    {
        if ($key == '_links' || $key == '_embedded') {
            // TODO: error handle;
            return $this;
        }
        $this->json[$key] = $val;
        return $this;
    }

    public function get(string $key)
    {
        return $this->json[$key];
    }

    public function has(string $key): ?bool
    {
        return isset($this->json[$key]);
    }

    public function setArray(array $arr): self
    {
        foreach ($arr as $k => $v) {
            $this->set($k, $v);
        }

        return $this;
    }

    public function unset(string $key): self
    {
        unset($this->json[$key]);
        return $this;
    }

    public function jsonSerialize(): ?array
    {
        $arr = [];

        if (count($this->links) > 0) {
            foreach ($this->links as $k => $v) {
                $arr['_links'][$k] = ['href' => $v];
            }
        }

        if (count($this->embedded) > 0) {
            foreach ($this->embedded as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $iv) {
                        $arr['_embedded'][$k][] = $iv->jsonSerialize();
                    }
                } else {
                    $arr['_embedded'][$k] = $v->jsonSerialize();
                }
            }
        }

        return array_merge($this->json, $arr);
    }
}
