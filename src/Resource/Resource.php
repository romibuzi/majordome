<?php

namespace Majordome\Resource;

class Resource implements ResourceInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $data;

    /**
     * @param string $id
     * @param string $type
     * @param array  $data
     */
    public function __construct($id, $type, array $data = [])
    {
        $this->id   = $id;
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        return $this->data;
    }
}
