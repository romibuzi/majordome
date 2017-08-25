<?php

namespace Majordome\Tests\Rule\AWS;

use Majordome\Rule\AWS\UnusedAMI;
use Majordome\Tests\Rule\AbstractRuleTest;

class UnusedAMITest extends AbstractRuleTest
{
    private static $ec2AMIs = [
        'iam.ec2.ami1',
        'iam.ec2.ami2',
        'iam.ec2.ami3',
    ];

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->rule = new UnusedAMI(self::$ec2AMIs);
    }

    /**
     * @dataProvider AMIResourcesProvider
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

    public function AMIResourcesProvider()
    {
        return [
            'AMI used by a EC2 instance' => [
                true,
                [
                    'ImageId' => 'iam.ec2.ami1', // in self::$ec2AMIs
                    'State' => 'completed',
                    "Architecture" => "x86_64",
                    "CreationDate" => "2016-07-26T09:15:39.000Z"
                ]
            ],
            'AMI not used by a EC2 instance' => [
                false,
                [
                    'ImageId' => 'iam.ec2.ami20', // not in self::$ec2AMIs
                    'State' => 'completed',
                    "Architecture" => "x86_64",
                    "CreationDate" => "2016-01-15T11:30:39.000Z"
                ]
            ],
            'Non AMI resource type' => [
                true,
                [
                    'LoadBalancerName' => 'XXXXXXXXXXX',
                    'DNSName' => 'XXXXXXXXXXX.eu-west-1.elb.amazonaws.com',
                ]
            ],
        ];
    }
}
