<?php

namespace Majordome\Tests\Resource;

use Majordome\Resource\AWSResourceType;
use Majordome\Resource\DefaultResource;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ResourceTest extends TestCase
{
    #[DataProvider('resourcesProvider')]
    public function testResource(string $id, string $type, array $data)
    {
        $resource = new DefaultResource($id, $type, $data);

        $this->assertSame($id, $resource->getId());
        $this->assertSame($type, $resource->getType());
        $this->assertSame($data, $resource->getData());
    }

    public static function resourcesProvider(): array
    {
        return [
            'some fake ELB resource' => [
                'SearchEngine',
                AWSResourceType::ELB->value,
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
                AWSResourceType::EC2->value,
                [
                    'PublicIp' => '52.208.3.172',
                    'PrivateIp' => '172.31.16.118',
                    'type' => 'm4.large',
                ]
            ],
        ];
    }
}
