<?php

namespace Majordome\Tests\Rule\AWS;

use Majordome\Rule\AWS\UnusedSnapshot;
use Majordome\Rule\Rule;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class UnusedSnapchotTest extends TestCase
{
    use ProphecyTrait;

    private Rule $rule;

    private static array $ebsVolumes = [
        'vol-AAAAAAAAAA',
        'vol-BBBBBBBBBB',
        'vol-CCCCCCCCCC',
    ];

    private static array $AMIs = [
        'ami-EEEEEEEE',
    ];

    public function setUp(): void
    {
        $this->rule = new UnusedSnapshot(self::$ebsVolumes, self::$AMIs);
    }

    #[DataProvider('snapshotResourcesProvider')]
    public function testRule(bool $expected, array $data)
    {
        $resource = $this->prophesize();
        $resource->willImplement('Majordome\Resource\Resource');
        $resource->getData()->willReturn($data)->shouldBeCalled();

        $result = $this->rule->isValid($resource->reveal());

        $this->assertSame($expected, $result);
    }

    public static function snapshotResourcesProvider(): array
    {
        return [
            'Snapshot of an existing Volume' => [
                true,
                [
                    'SnapshotId' => 'snap-AAAAAAAAAA',
                    'VolumeId' => 'vol-AAAAAAAAAA', // in self::$ebsVolumes
                    'State' => 'completed',
                    'Description' => 'Created by CreateImage(i-AAAAAAAAAA) for ami-AAAAAAAAAA from vol-AAAAAAAAAA'
                ]
            ],
            'Snapshot of an non existing Volume' => [
                false,
                [
                    'SnapshotId' => 'snap-0q7c0ca0',
                    'VolumeId' => 'vol-DDDDDDDDD', // not in self::$ebsVolumes
                    'State' => 'completed',
                    'Description' => 'Created by CreateImage(i-DDDDDDDDD) for ami-DDDDDDDDD from vol-DDDDDDDDD'
                ]
            ],
            'Snapshot of an non existing Volume but existing AMI' => [
                true,
                [
                    'SnapshotId' => 'snap-EEEEEEEE',
                    'VolumeId' => 'vol-EEEEEEEE', // not in self::$ebsVolumes
                    'State' => 'completed',

                    // in self::$AMIs
                    'Description' => 'Created by CreateImage(i-EEEEEEEE) for ami-EEEEEEEE from vol-EEEEEEEE'
                ]
            ],
            'Snapshot of an non existing Volume and non existing AMI' => [
                false,
                [
                    'SnapshotId' => 'snap-HHHHHH',
                    'VolumeId' => 'vol-HHHHHH', // not in self::$ebsVolumes
                    'State' => 'completed',

                    // not in self::$AMIs
                    'Description' => 'Created by CreateImage(i-HHHHHH) for ami-HHHHHH from vol-HHHHHH'
                ]
            ],
            'Non Snapshot resource type' => [
                true,
                [
                    'LoadBalancerName' => 'XXXXXXXXXXXXX',
                    'DNSName' => 'XXXXXXXXXXXXX.eu-west-1.elb.amazonaws.com',
                ]
            ],
        ];
    }
}
