<?php

namespace Majordome\Tests\Crawler;

use Aws\Ec2\Ec2Client;
use Aws\ElastiCache\ElastiCacheClient;
use Aws\ElasticLoadBalancing\ElasticLoadBalancingClient;
use Aws\Rds\RdsClient;
use Majordome\Crawler\AWSCrawler;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class AWSCrawlerTest extends TestCase
{
    use ProphecyTrait;

    private Ec2Client|ObjectProphecy $ec2Client;
    private ElasticLoadBalancingClient|ObjectProphecy $elbClient;
    private RdsClient|ObjectProphecy $rdsClient;
    private ElastiCacheClient|ObjectProphecy $elastiCacheClient;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->ec2Client = $this->prophesize('Aws\Ec2\Ec2Client');
        $this->elbClient = $this->prophesize('Aws\ElasticLoadBalancing\ElasticLoadBalancingClient');
        $this->rdsClient = $this->prophesize('Aws\Rds\RdsClient');
        $this->elastiCacheClient = $this->prophesize('Aws\ElastiCache\ElastiCacheClient');
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

        $crawler = new AWSCrawler(
            $this->ec2Client->reveal(),
            $this->elbClient->reveal(),
            $this->rdsClient->reveal(),
            $this->elastiCacheClient->reveal()
        );
        $resources = $crawler->getEBSResources();

        $this->assertSame(count($data['Volumes']), count($resources));
        foreach ($resources as $resource) {
            $this->assertInstanceOf(
                'Majordome\Resource\Resource',
                $resource,
                'AWSCrawler should return resources objects implementing Resource'
            );
            $this->assertContains($resource->getId(), [$volumeId1, $volumeId2]);
            $this->assertContains($resource->getData(), $data['Volumes']);
        }
    }
}
