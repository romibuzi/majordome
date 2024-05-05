<?php

namespace Majordome\Resource;

class DefaultResource implements Resource
{
    private string $id;

    private string $type;

    private array $data;

    public function __construct(string $id, string $type, array $data = [])
    {
        $this->id   = $id;
        $this->type = $type;
        $this->data = $data;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
