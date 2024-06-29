<?php

namespace Majordome\Rule\AWS;

use Majordome\Resource\Resource;
use Majordome\Rule\Rule;

class UnusedSecurityGroup implements Rule
{
    private static array $securityGroupsIds;

    /**
     * @param string[] $securityGroups
     */
    public function __construct(array $securityGroups)
    {
        self::$securityGroupsIds = $securityGroups;
    }

    /**
     * {@inheritDoc}
     */
    public function isValid(Resource $resource): bool
    {
        $data = $resource->getData();

        // this rule runs only for Security Group resources
        if (!array_key_exists('GroupId', $data)) {
            return true;
        }

        if (!in_array($data['GroupId'], self::$securityGroupsIds)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public static function getName(): string
    {
        return 'UnusedSecurityGroup';
    }

    /**
     * {@inheritDoc}
     */
    public static function getDescription(): string
    {
        $desc = 'Consider as invalid a Security Group that is unused by any other AWS resource '.
        'that makes usage of it (ELB, EC2, RDS, ElastiCache)';

        return str_replace("\n", "", $desc);
    }
}
