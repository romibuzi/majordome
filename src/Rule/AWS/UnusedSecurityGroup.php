<?php

namespace Majordome\Rule\AWS;

use Majordome\Resource\ResourceInterface;
use Majordome\Rule\RuleInterface;

class UnusedSecurityGroup implements RuleInterface
{
    /**
     * @var array
     */
    private static $securityGroupsIds;

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
    public function isValid(ResourceInterface $resource)
    {
        $data = $resource->getData();

        // this rule is only runned for Security Group resources
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
    public function getName()
    {
        return 'UnusedSecurityGroup';
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        $desc = 'Consider as invalid a Security Group that is unused by any other AWS resource '.
        'that makes usage of it (ELB, EC2, RDS, ElastiCache)';

        return str_replace("\n", "", $desc);
    }
}
