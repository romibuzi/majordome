<?php

namespace Majordome\Crawler;

use Aws\Ec2\Ec2Client;
use Aws\ElastiCache\ElastiCacheClient;
use Aws\ElasticLoadBalancing\ElasticLoadBalancingClient;
use Aws\Rds\RdsClient;
use Majordome\Resource\AWSResourceType;
use Majordome\Resource\DefaultResource;
use Majordome\Resource\Resource;

class AWSCrawler
{
    private Ec2Client $ec2Client;
    private ElasticLoadBalancingClient $elbClient;
    private RdsClient $rdsClient;
    private ElastiCacheClient $elastiCacheClient;

    public function __construct(
        Ec2Client                  $ec2Client,
        ElasticLoadBalancingClient $elbClient,
        RdsClient                  $rdsClient,
        ElastiCacheClient          $elastiCacheClient,
    ) {
        $this->ec2Client = $ec2Client;
        $this->elbClient = $elbClient;
        $this->rdsClient = $rdsClient;
        $this->elastiCacheClient = $elastiCacheClient;
    }

    /**
     * @param string $ownerId
     *
     * @return Resource[]
     */
    public function getAMIResources(string $ownerId): array
    {
        $amiData = $this->ec2Client->describeImages(['Owners' => [$ownerId]])['Images'];

        return $this->buildResources('ImageId', AWSResourceType::AMI, $amiData);
    }

    public function getEBSResources(): array
    {
        $ebsData = $this->ec2Client->describeVolumes()['Volumes'];

        return $this->buildResources('VolumeId', AWSResourceType::EBS, $ebsData);
    }

    public function getElasticIpResources(): array
    {
        $elasticIPData = $this->ec2Client->describeAddresses()['Addresses'];

        return $this->buildResources('PublicIp', AWSResourceType::EIP, $elasticIPData);
    }

    public function getELBResources(): array
    {
        static $elbData = [];

        if (empty($elbData)) {
            $elbData = $this->elbClient->describeLoadBalancers()['LoadBalancerDescriptions'];
        }

        return $this->buildResources('LoadBalancerName', AWSResourceType::ELB, $elbData);
    }

    public function getSecurityGroupResources(): array
    {
        $sgData = $this->ec2Client->describeSecurityGroups()['SecurityGroups'];

        return $this->buildResources('GroupId', AWSResourceType::SG, $sgData);
    }

    /**
     * @param string $ownerId
     *
     * @return Resource[]
     */
    public function getSnapshotResources(string $ownerId): array
    {
        $snapshotsData = $this->ec2Client->describeSnapshots(['OwnerIds' => [$ownerId]])['Snapshots'];

        return $this->buildResources('SnapshotId', AWSResourceType::SNAPSHOT, $snapshotsData);
    }

    /**
     * @return string[]
     */
    public function listEC2AMIs(): array
    {
        $ec2AMIs = [];
        foreach ($this->listEC2Instances() as $instance) {
            if (!in_array($instance['ImageId'], $ec2AMIs)) {
                $ec2AMIs[] = $instance['ImageId'];
            }
        }

        return $ec2AMIs;
    }

    /**
     * @return string[]
     */
    public function listEC2SecurityGroups(): array
    {
        $ec2SecurityGroups = [];

        foreach ($this->listEC2Instances() as $instance) {
            foreach ($instance['SecurityGroups'] as $securityGroup) {
                if (!in_array($securityGroup['GroupId'], $ec2SecurityGroups)) {
                    $ec2SecurityGroups[] = $securityGroup['GroupId'];
                }
            }
        }

        return $ec2SecurityGroups;
    }

    /**
     * @return string[]
     */
    public function listElastiCacheSecurityGroups(): array
    {
        $elastiCacheSecurityGroups = [];

        $elastiCacheData = $this->elastiCacheClient->describeCacheClusters();

        foreach ($elastiCacheData['CacheClusters'] as $cacheCluster) {
            foreach ($cacheCluster['SecurityGroups'] as $securityGroup) {
                if (!in_array($securityGroup['SecurityGroupId'], $elastiCacheSecurityGroups)) {
                    $elastiCacheSecurityGroups[] = $securityGroup['SecurityGroupId'];
                }
            }
        }

        return $elastiCacheSecurityGroups;
    }

    /**
     * @return string[]
     */
    public function listELBSecurityGroups(): array
    {
        $elbSecurityGroups = [];

        $elbResources = $this->getELBResources();

        foreach ($elbResources as $elb) {
            foreach ($elb->getData()['SecurityGroups'] as $securityGroup) {
                if (!in_array($securityGroup, $elbSecurityGroups)) {
                    $elbSecurityGroups[] = $securityGroup;
                }
            }
        }

        return $elbSecurityGroups;
    }

    /**
     * @return string[]
     */
    public function listRdsSecurityGroups(): array
    {
        $rdsSecurityGroups = [];

        $rdsData = $this->rdsClient->describeDBInstances();

        foreach ($rdsData['DBInstances'] as $rdsInstance) {
            foreach ($rdsInstance['VpcSecurityGroups'] as $securityGroup) {
                if (!in_array($securityGroup['VpcSecurityGroupId'], $rdsSecurityGroups)) {
                    $rdsSecurityGroups[] = $securityGroup['VpcSecurityGroupId'];
                }
            }
        }

        return $rdsSecurityGroups;
    }

    /**
     * @param string $key
     * @param AWSResourceType $type
     * @param array $data
     *
     * @return Resource[]
     */
    private function buildResources(string $key, AWSResourceType $type, array $data): array
    {
        $resources = [];

        foreach ($data as $item) {
            $resources[] = new DefaultResource($item[$key], $type->value, $item);
        }

        return $resources;
    }

    /**
     * @return \Generator
     */
    private function listEC2Instances(): \Generator
    {
        static $result = null;

        if (null === $result) {
            $result = $this->ec2Client->describeInstances();
        }

        $reservations = $result['Reservations'];
        foreach ($reservations as $reservation) {
            $instances = $reservation['Instances'];
            foreach ($instances as $instance) {
                yield $instance;
            }
        }
    }
}
