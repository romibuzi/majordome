<?php

namespace Majordome\Tests\Rule\AWS;

use Majordome\Rule\AWS\ELBWithoutInstances;
use Majordome\Rule\Rule;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class ELBWithoutInstancesTest extends TestCase
{
    use ProphecyTrait;

    private Rule $rule;

    public function setUp(): void
    {
        $this->rule = new ELBWithoutInstances();
    }

    #[DataProvider('elasticLoadBalancerResourcesProvider')]
    public function testRule(bool $expected, array $data)
    {
        $resource = $this->prophesize();
        $resource->willImplement('Majordome\Resource\Resource');
        $resource->getData()->willReturn($data)->shouldBeCalled();

        $result = $this->rule->isValid($resource->reveal());

        $this->assertSame($expected, $result);
    }

    public static function elasticLoadBalancerResourcesProvider(): array
    {
        return [
            'ELB with mutliples instances attached to it' => [
                true,
                [
                    'LoadBalancerName' => 'XXXXX',
                    'DNSName' => 'XXXXX.eu-east-1.elb.amazonaws.com',
                    'Instances' => [
                        [
                            'InstanceId' => 'i-XXXXX',
                        ],
                        [
                            'InstanceId' => 'i-XXXXXXXX',
                        ],
                    ]
                ]
            ],
            'ELB with one instance attached to it' => [
                true,
                [
                    'LoadBalancerName' => 'YYYYY',
                    'DNSName' => 'YYYYY.eu-west-1.elb.amazonaws.com',
                    'Instances' => [
                        [
                            'InstanceId' => 'i-XXXXX',
                        ],
                    ]
                ]
            ],
            'ELB without instances attached to it' => [
                false,
                [
                    'LoadBalancerName' => 'ZZZZZZZZZZ',
                    'DNSName' => 'ZZZZZZZZZZ.eu-west-2.elb.amazonaws.com',
                    'Instances' => []
                ]
            ],
            'Non ELB resource type' => [
                true,
                [
                    'VolumeId' => 'vol-XXX',
                    'Size' => 8,
                    'AvailabilityZone' => 'eu-west-1c',
                    'State' => 'in-use',
                    'CreateTime' => (new \DateTime('now'))->modify('-1 day'),
                ]
            ],
        ];
    }
}
