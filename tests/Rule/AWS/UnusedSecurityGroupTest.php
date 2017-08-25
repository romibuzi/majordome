<?php

namespace Majordome\Tests\Rule\AWS;

use Majordome\Rule\AWS\UnusedSecurityGroup;
use Majordome\Tests\Rule\AbstractRuleTest;

class UnusedSecurityGroupTest extends AbstractRuleTest
{
    private static $ec2SecurityGroups = [
        'sg-AAAAAAAAA',
        'sg-BBBBBBBBB',
        'sg-XXXXXXXXX',
    ];

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->rule = new UnusedSecurityGroup(self::$ec2SecurityGroups);
    }

    /**
     * @dataProvider securityGroupResourcesProvider
     *
     * @param bool  $expected
     * @param array $data
     */
    public function testRule($expected, array $data)
    {
        $resource = $this->prophesize();
        $resource->willImplement('Majordome\Resource\ResourceInterface');
        $resource->getData()->willReturn($data)->shouldBeCalled();

        $result = $this->rule->isValid($resource->reveal());

        $this->assertSame($expected, $result);
    }

    public function securityGroupResourcesProvider()
    {
        return [
            'Security Group used by an EC2 instance' => [
                true,
                [
                    'GroupName' => 'XXXXXXXXX',
                    'GroupId' => 'sg-XXXXXXXXX', // in self::$ec2SecurityGroups
                    'Description' => 'Securiity group for XXXXXXXXX',
                ]
            ],
            'Security Group not used by an EC2 instance' => [
                false,
                [
                    'GroupName' => 'YYYYYYY',
                    'GroupId' => 'sg-YYYYYYY',  // not in self::$ec2SecurityGroups
                    'Description' => 'Securiity group for YYYYYYY',
                ]
            ],
            'Non Security Group resource type' => [
                true,
                [
                    'LoadBalancerName' => 'XXXXXXXXX',
                    'DNSName' => 'XXXXXXXXX.eu-west-1.elb.amazonaws.com',
                ]
            ],
        ];
    }
}
