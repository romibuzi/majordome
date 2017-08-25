<?php

namespace Majordome\Resource;

/**
 * The ResourceUrlGeneratorInterface implementing a logic to retrieve the access URL of a given resource.
 * It could be either a web administration URL or a REST API endpoint
 */
interface ResourceUrlGeneratorInterface
{
    /**
     * @param ResourceInterface $resource
     *
     * @return string
     */
    public function generateUrl(ResourceInterface $resource);
}
