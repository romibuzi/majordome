<?php

namespace Majordome\Tests\Crawler;

use Majordome\Crawler\AWSCrawler;
use Prophecy\Prophecy\ObjectProphecy;

class AWSCrawlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Aws\Sdk|ObjectProphecy */
    private $awsSdk;
    /** @var \Aws\Ec2\Ec2Client|ObjectProphecy */
    private $ec2Client;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->awsSdk    = $this->prophesize('Aws\Sdk');
        $this->ec2Client = $this->prophesize('Aws\Ec2\Ec2Client');
    }

    public function testCrawlerWillInitializeAWSClients()
    {
        $this->awsSdk->createEc2()->shouldBeCalled();
        $this->awsSdk->createElasticLoadBalancing()->shouldBeCalled();
        $this->awsSdk->createRds()->shouldBeCalled();
        $this->awsSdk->createElastiCache()->shouldBeCalled();

        new AWSCrawler($this->awsSdk->reveal());
    }

    public function testGetEBSResources()
    {
        $volumeId1 = 'vol-11111111';
        $volumeId2 = 'vol-22222222';
        $data = [
           'Volumes' => [
               [
                   'VolumeId' => $volumeId1,
                   'Size' => 8,
                   'SnapshotId' => 'snap-11111111',
                   'AvailabilityZone' => 'eu-west-1c',
                   'State' => 'in-use',
                   'CreateTime' => (new \DateTime('now'))->modify('-45 day')
               ],
               [
                   'VolumeId' => $volumeId2,
                   'Size' => 20,
                   'SnapshotId' => 'snap-22222222',
                   'AvailabilityZone' => 'eu-west-1a',
                   'State' => 'available',
                   'CreateTime' => (new \DateTime('now'))->modify('-90 day')
               ]
           ]
        ];
        $this->ec2Client->describeVolumes()->WillReturn($data)->shouldBeCalled();
        $this->awsSdk->createEc2()->WillReturn($this->ec2Client)->shouldBeCalled();

        $this->awsSdk->createElasticLoadBalancing()->shouldBeCalled();
        $this->awsSdk->createRds()->shouldBeCalled();
        $this->awsSdk->createElastiCache()->shouldBeCalled();

        $crawler   = new AWSCrawler($this->awsSdk->reveal());
        $resources = $crawler->getEBSResources();

        $this->assertSame(count($data['Volumes']), count($resources));
        foreach ($resources as $resource) {
            $this->assertInstanceOf(
                'Majordome\Resource\ResourceInterface',
                $resource,
                'AWSCrawler should return resources objects implementing ResourceInterface'
            );
            $this->assertContains($resource->getId(), [$volumeId1, $volumeId2]);
            $this->assertContains($resource->getData(), $data['Volumes']);
        }
    }
}
