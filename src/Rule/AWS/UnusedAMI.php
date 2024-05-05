<?php

namespace Majordome\Rule\AWS;

use Majordome\Resource\Resource;
use Majordome\Rule\Rule;

class UnusedAMI implements Rule
{
    private array $ec2AMIs;

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
    public function isValid(Resource $resource): bool
    {
        $data = $resource->getData();

        // this rule runs only for AMI resources
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
    public static function getName(): string
    {
        return 'UnusedAMI';
    }

    /**
     * {@inheritDoc}
     */
    public static function getDescription(): string
    {
        return 'Consider as invalid a AMI not used by any EC2 instance';
    }
}
