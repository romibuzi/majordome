<?php

namespace Majordome\Rule\AWS;

use Majordome\Resource\Resource;
use Majordome\Rule\Rule;

class ELBWithoutInstances implements Rule
{
    /**
     * {@inheritDoc}
     */
    public function isValid(Resource $resource): bool
    {
        $data = $resource->getData();

        // this rule runs only for ELB resources
        if (!array_key_exists('LoadBalancerName', $data)) {
            return true;
        }

        if (count($data['Instances']) === 0) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public static function getName(): string
    {
        return 'ELBWithoutInstances';
    }

    /**
     * {@inheritDoc}
     */
    public static function getDescription(): string
    {
        return 'Consider as invalid a ELB with 0 instance attached behind it';
    }
}
