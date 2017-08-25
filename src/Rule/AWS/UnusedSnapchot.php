<?php

namespace Majordome\Rule\AWS;

use Majordome\Resource\ResourceInterface;
use Majordome\Rule\RuleInterface;

class UnusedSnapchot implements RuleInterface
{
    private $ebsVolumesIds;
    private $ec2AMIs;

    /**
     * @param string[] $ebsVolumesIds
     * @param string[] $ec2AMIs
     */
    public function __construct(array $ebsVolumesIds, array $ec2AMIs)
    {
        $this->ebsVolumesIds = $ebsVolumesIds;
        $this-> ec2AMIs      = $ec2AMIs;
    }

    /**
     * {@inheritDoc}
     */
    public function isValid(ResourceInterface $resource)
    {
        $data = $resource->getData();

        // this rule is only runned for Snapshots resources
        if (!array_key_exists('SnapshotId', $data)) {
            return true;
        }

        // the snapshot is for an existing EBS volume => no need to go further and consider it valid
        if (in_array($data['VolumeId'], $this->ebsVolumesIds)) {
            return true;
        }

        // "Created by CreateImage(i-81c33a0a) for ami-b4fbafc3 from vol-7049bpo8m"
        $pattern = "/^Created by CreateImage\((.*?)\) for (.*?) from (.*?)$/";

        // check if the snapshot is used by an existing AMI
        if (preg_match($pattern, $data['Description'], $matches) && in_array($matches[2], $this->ec2AMIs)) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'UnusedSnapchot';
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        $desc = 'Consider as invalid a Snapshot of a EBS Volume that doesn\'t or no more exists '.
        'and does not belong to a existing AMI';

        return str_replace("\n", "", $desc);
    }
}
