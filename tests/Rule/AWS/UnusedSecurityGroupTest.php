<?php

namespace Majordome\Tests\Rule\AWS;

use Majordome\Rule\AWS\UnusedSecurityGroup;
use Majordome\Rule\Rule;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class UnusedSecurityGroupTest extends TestCase
{
    use ProphecyTrait;

    private Rule $rule;

    private static array $ec2SecurityGroups = [
        'sg-AAAAAAAAA',
        'sg-BBBBBBBBB',
        'sg-XXXXXXXXX',
    ];

    public function setUp(): void
    {
        $this->rule = new UnusedSecurityGroup(self::$ec2SecurityGroups);
    }

    #[DataProvider('securityGroupResourcesProvider')]
    public function testRule(bool $expected, array $data)
    {
        $resource = $this->prophesize();
        $resource->willImplement('Majordome\Resource\Resource');
        $resource->getData()->willReturn($data)->shouldBeCalled();

        $result = $this->rule->isValid($resource->reveal());

        $this->assertSame($expected, $result);
    }

    public static function securityGroupResourcesProvider(): array
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
