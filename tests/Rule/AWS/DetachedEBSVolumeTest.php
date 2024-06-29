<?php

namespace Majordome\Tests\Rule\AWS;

use Majordome\Rule\AWS\DetachedEBSVolume;
use Majordome\Rule\Rule;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class DetachedEBSVolumeTest extends TestCase
{
    use ProphecyTrait;

    private Rule $rule;

    public function setUp(): void
    {
        $this->rule = new DetachedEBSVolume();
    }

    #[DataProvider('ebsVolumeResourcesProvider')]
    public function testRule(bool $expected, array $data)
    {
        $resource = $this->prophesize();
        $resource->willImplement('Majordome\Resource\Resource');
        $resource->getData()->willReturn($data)->shouldBeCalled();

        $result = $this->rule->isValid($resource->reveal());

        $this->assertSame($expected, $result);
    }

    public static function ebsVolumeResourcesProvider(): array
    {
        return [
            'EBS Volume in use (attached to an EC2 instance)' => [
                true,
                [
                    'VolumeId' => 'vol-XXXXXXXXXXX',
                    'Size' => 8,
                    'AvailabilityZone' => 'eu-west-1c',
                    'State' => 'in-use',
                    'CreateTime' => (new \DateTime('now'))->modify('-45 day')
                ]
            ],
            'EBS Volume available (not attached to an EC2 instance)' => [
                false,
                [
                    'VolumeId' => 'vol-YYYYYYYY',
                    'Size' => 20,
                    'AvailabilityZone' => 'eu-west-1a',
                    'State' => 'available',
                    'CreateTime' => (new \DateTime('now'))->modify('-90 day'),
                ]
            ],
            'Non EBS Volume resource type' => [
                true,
                [
                    'LoadBalancerName' => 'XXXXXXX',
                    'DNSName' => 'XXXXXXX.eu-west-1.elb.amazonaws.com',
                ]
            ],
        ];
    }
}
