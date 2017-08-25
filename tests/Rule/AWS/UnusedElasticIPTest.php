<?php

namespace Majordome\Tests\Rule\AWS;

use Majordome\Rule\AWS\UnusedElasticIP;
use Majordome\Tests\Rule\AbstractRuleTest;

class UnusedElasticIPTest extends AbstractRuleTest
{
    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->rule = new UnusedElasticIP();
    }

    /**
     * @dataProvider elasticIpResourcesProvider
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

    public function elasticIpResourcesProvider()
    {
        return [
            'elasticIp attached to an EC2 instance' => [
                true,
                [
                    'InstanceId' => 'i-XXXXXXXXXXX',
                    'PublicIp' => '50.00.00.01',
                    'AllocationId' => 'eipalloc-XXXXX',
                    'Domain' => 'vpc'
                ]
            ],
            'elasticIp not attached to an EC2 instance' => [
                false,
                [
                    'PublicIp' => '51.00.00.01',
                    'AllocationId' => 'eipalloc-ZZZZZ',
                    'Domain' => 'vpc',
                ]
            ],
            'non elasticIp resource type' => [
                true,
                [
                    'LoadBalancerName' => 'XXXXXXXX',
                    'DNSName' => 'XXXXXXXX.eu-west-1.elb.amazonaws.com',
                ]
            ],
        ];
    }
}
