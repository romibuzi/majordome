<?php

namespace Majordome\Resource;

interface Resource
{
    public function getId(): string;

    public function getType(): string;

    public function getData(): array;
}
