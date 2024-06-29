<?php

namespace Majordome\Rule\AWS;

use Majordome\Resource\Resource;
use Majordome\Rule\Rule;

class UnusedElasticIP implements Rule
{
    /**
     * {@inheritDoc}
     */
    public function isValid(Resource $resource): bool
    {
        $data = $resource->getData();

        // this rule runs only for Elastic IP resources
        if (!array_key_exists('PublicIp', $data)) {
            return true;
        }

        // if the Elastic IP isn't attached to either any EC2 instance or network interface (like a NAT)
        // consider it as unused
        if (!array_key_exists('InstanceId', $data) && !array_key_exists('NetworkInterfaceOwnerId', $data)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public static function getName(): string
    {
        return 'UnusedElasticIP';
    }

    /**
     * {@inheritDoc}
     */
    public static function getDescription(): string
    {
        return 'Consider as invalid a Elastic IP not attached to any EC2 instance or network interface';
    }
}
