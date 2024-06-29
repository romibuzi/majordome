<?php

namespace Majordome\Resource\Url;

use Majordome\Resource\Resource;

/**
 * The ResourceUrlGeneratorInterface implementing a logic to retrieve the access URL of a given resource.
 * It could be either a web administration URL or a REST API endpoint
 */
interface ResourceUrlGenerator
{
    /**
     * @param Resource $resource
     *
     * @return string
     */
    public function generateUrl(Resource $resource);
}
