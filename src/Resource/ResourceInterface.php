<?php

namespace Majordome\Resource;

interface ResourceInterface
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return array
     */
    public function getData();
}
