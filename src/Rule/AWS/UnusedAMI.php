<?php

namespace Majordome\Rule\AWS;

use Majordome\Resource\ResourceInterface;
use Majordome\Rule\RuleInterface;

class UnusedAMI implements RuleInterface
{
    private $ec2AMIs;

    /**
     * @param string[] $ec2AMIs
     */
    public function __construct(array $ec2AMIs)
    {
        $this->ec2AMIs = $ec2AMIs;
    }

    /**
     * {@inheritDoc}
     */
    public function isValid(ResourceInterface $resource)
    {
        $data = $resource->getData();

        // this rule is only runned for AMI resources
        if (!array_key_exists('ImageId', $data)) {
            return true;
        }

        if (!in_array($data['ImageId'], $this->ec2AMIs)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'UnusedAMI';
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return 'Consider as invalid a AMI not used by any EC2 instance';
    }
}
