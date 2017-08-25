<?php

namespace Majordome\Tests\Resource;

use Majordome\Resource\AWSResourceType;
use Majordome\Resource\Resource;

class ResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider resourcesProvider
     *
     * @param string $id
     * @param string $type
     * @param array $data
     */
    public function testResource($id, $type, array $data)
    {
        $resource = new Resource($id, $type, $data);

        $this->assertSame($id, $resource->getId());
        $this->assertSame($type, $resource->getType());
        $this->assertSame($data, $resource->getData());
    }

    public function resourcesProvider()
    {
        return [
            'some fake ELB resource' => [
                'SearchEngine',
                AWSResourceType::ELB,
                [
                    'LoadBalancerName' => 'SearchEngine',
                    'DNSName' => 'SearchEngine-833211589.eu-west-1.elb.amazonaws.com',
                    'Instances' => [
                        [
                            'InstanceId' => 'i-8a4b7002',
                        ],
                    ]
                ]
            ],
            'some fake EC2 resource' => [
                'i-8a4b7002',
                AWSResourceType::EC2,
                [
                    'PublicIp' => '52.208.3.172',
                    'PrivateIp' => '172.31.16.118',
                    'type' => 'm4.large',
                ]
            ],
        ];
    }
}
