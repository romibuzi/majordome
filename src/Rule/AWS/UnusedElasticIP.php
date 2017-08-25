<?php

namespace Majordome\Rule\AWS;

use Majordome\Resource\ResourceInterface;
use Majordome\Rule\RuleInterface;

class UnusedElasticIP implements RuleInterface
{
    /**
     * {@inheritDoc}
     */
    public function isValid(ResourceInterface $resource)
    {
        $data = $resource->getData();

        // this rule is only runned for Elastic IP resources
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
    public function getName()
    {
        return 'UnusedElasticIP';
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return 'Consider as invalid a Elastic IP not attached to any EC2 instance or network interface';
    }
}
