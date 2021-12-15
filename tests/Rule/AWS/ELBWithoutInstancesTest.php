<?php

namespace Majordome\Tests\Rule\AWS;

use Majordome\Rule\AWS\ELBWithoutInstances;
use Majordome\Tests\Rule\AbstractRuleTest;
use Prophecy\PhpUnit\ProphecyTrait;

class ELBWithoutInstancesTest extends AbstractRuleTest
{
    use ProphecyTrait;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->rule = new ELBWithoutInstances();
    }

    /**
     * @dataProvider elasticLoadBalancerResourcesProvider
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

    public function elasticLoadBalancerResourcesProvider()
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
