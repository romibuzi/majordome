<?php

namespace Majordome\Crawler;

use Aws\Sdk;
use Majordome\Resource\AWSResourceType;
use Majordome\Resource\Resource;
use Majordome\Resource\ResourceInterface;

class AWSCrawler
{
    private $ec2Client;
    private $elbClient;
    private $rdsClient;
    private $elasticacheClient;

    public function __construct(Sdk $awsSdk)
    {
        $this->ec2Client = $awsSdk->createEc2();
        $this->elbClient = $awsSdk->createElasticLoadBalancing();
        $this->rdsClient = $awsSdk->createRds();
        $this->elasticacheClient = $awsSdk->createElastiCache();
    }

    /**
     * @param string $ownerId
     *
     * @return ResourceInterface[]
     */
    public function getAMIResources($ownerId)
    {
        $amiData = $this->ec2Client->describeImages(['Owners' => [$ownerId]])['Images'];

        return $this->buildResources('ImageId', AWSResourceType::AMI, $amiData);
    }

    public function getEBSResources()
    {
        $ebsData = $this->ec2Client->describeVolumes()['Volumes'];

        return $this->buildResources('VolumeId', AWSResourceType::EBS, $ebsData);
    }

    public function getElasticIpResources()
    {
        $elasticIPData = $this->ec2Client->describeAddresses()['Addresses'];

        return $this->buildResources('PublicIp', AWSResourceType::EIP, $elasticIPData);
    }

    public function getELBResources()
    {
        static $elbData = [];

        if (empty($elbData)) {
            $elbData = $this->elbClient->describeLoadBalancers()['LoadBalancerDescriptions'];
        }

        return $this->buildResources('LoadBalancerName', AWSResourceType::ELB, $elbData);
    }

    public function getSecurityGroupResources()
    {
        $sgData = $this->ec2Client->describeSecurityGroups()['SecurityGroups'];

        return $this->buildResources('GroupId', AWSResourceType::SG, $sgData);
    }

    /**
     * @param string $ownerId
     *
     * @return ResourceInterface[]
     */
    public function getSnapshotResources($ownerId)
    {
        $snapshotsData = $this->ec2Client->describeSnapshots(['OwnerIds' => [$ownerId]])['Snapshots'];

        return $this->buildResources('SnapshotId', AWSResourceType::SNAPSHOT, $snapshotsData);
    }

    /**
     * @return string[]
     */
    public function listEC2AMIs()
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
    public function listEC2SecurityGroups()
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
    public function listElasticacheSecurityGroups()
    {
        $elasticacheSecurityGroups = [];

        $elasticacheData = $this->elasticacheClient->describeCacheClusters();

        foreach ($elasticacheData['CacheClusters'] as $cacheCluster) {
            foreach ($cacheCluster['SecurityGroups'] as $securityGroup) {
                if (!in_array($securityGroup['SecurityGroupId'], $elasticacheSecurityGroups)) {
                    $elasticacheSecurityGroups[] = $securityGroup['SecurityGroupId'];
                }
            }
        }

        return $elasticacheSecurityGroups;
    }

    /**
     * @return string[]
     */
    public function listELBSecurityGroups()
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
    public function listRdsSecurityGroups()
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
     * @param string $type
     * @param array  $data
     *
     * @return ResourceInterface[]
     */
    private function buildResources($key, $type, array $data)
    {
        $resources = [];

        foreach ($data as $item) {
            $resources[] = new Resource($item[$key], $type, $item);
        }

        return $resources;
    }

    /**
     * @return \Generator
     */
    private function listEC2Instances()
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
