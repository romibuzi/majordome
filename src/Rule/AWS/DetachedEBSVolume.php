<?php

namespace Majordome\Rule\AWS;

use Majordome\Resource\ResourceInterface;
use Majordome\Rule\RuleInterface;

class DetachedEBSVolume implements RuleInterface
{
    /** State when the volume isn't attached to any EC2 instance */
    const AVAILABLE_VOLUME = 'available';

    /**
     * {@inheritDoc}
     */
    public function isValid(ResourceInterface $resource)
    {
        $data = $resource->getData();

        // this rule is only runned for EBS Volume resources
        if (!array_key_exists('VolumeId', $data)) {
            return true;
        }

        if ($data['State'] === self::AVAILABLE_VOLUME) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'DetachedEBSVolume';
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return 'Consider as invalid a EBS Volume not attached to any EC2 instance';
    }
}
