<?php

namespace Majordome\Rule\AWS;

use Majordome\Resource\Resource;
use Majordome\Rule\Rule;

class DetachedEBSVolume implements Rule
{
    /** State when the volume isn't attached to any EC2 instance */
    final const AVAILABLE_VOLUME = 'available';

    /**
     * {@inheritDoc}
     */
    public function isValid(Resource $resource): bool
    {
        $data = $resource->getData();

        // this rule runs only for EBS Volume resources
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
    public static function getName(): string
    {
        return 'DetachedEBSVolume';
    }

    /**
     * {@inheritDoc}
     */
    public static function getDescription(): string
    {
        return 'Consider as invalid a EBS Volume not attached to any EC2 instance';
    }
}
