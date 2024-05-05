<?php

namespace Majordome\Tests\Rule\AWS;

use Majordome\Rule\AWS\UnusedElasticIP;
use Majordome\Rule\Rule;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class UnusedElasticIPTest extends TestCase
{
    use ProphecyTrait;

    private Rule $rule;

    public function setUp(): void
    {
        $this->rule = new UnusedElasticIP();
    }

    #[DataProvider('elasticIpResourcesProvider')]
    public function testRule(bool $expected, array $data)
    {
        $resource = $this->prophesize();
        $resource->willImplement('Majordome\Resource\Resource');
        $resource->getData()->willReturn($data)->shouldBeCalled();

        $result = $this->rule->isValid($resource->reveal());

        $this->assertSame($expected, $result);
    }

    public static function elasticIpResourcesProvider(): array
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
