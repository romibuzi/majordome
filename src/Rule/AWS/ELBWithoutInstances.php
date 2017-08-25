<?php

namespace Majordome\Rule\AWS;

use Majordome\Resource\ResourceInterface;
use Majordome\Rule\RuleInterface;

class ELBWithoutInstances implements RuleInterface
{
    /**
     * {@inheritDoc}
     */
    public function isValid(ResourceInterface $resource)
    {
        $data = $resource->getData();

        // this rule is only runned for ELB resources
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
    public function getName()
    {
        return 'ELBWithoutInstances';
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return 'Consider as invalid a ELB with 0 instance attached behind it';
    }
}
