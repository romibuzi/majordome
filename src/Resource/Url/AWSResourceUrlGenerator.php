<?php

namespace Majordome\Resource\Url;

use Majordome\Resource\AWSResourceType;
use Majordome\Resource\Resource;

class AWSResourceUrlGenerator implements ResourceUrlGenerator
{
    const WEB_CONSOLE_URL = 'https://console.aws.amazon.com';

    private string $ec2PanelUrl;

    public function __construct(string $awsRegion = 'eu-central-1')
    {
        $this->ec2PanelUrl = self::WEB_CONSOLE_URL . '/ec2/home?region=' . $awsRegion;
    }

    public function generateUrl(Resource $resource): string
    {
        $awsResourceType = AWSResourceType::tryFrom($resource->getType());
        if ($awsResourceType === null) {
            return self::WEB_CONSOLE_URL;
        }

        $section = match ($awsResourceType) {
            AWSResourceType::AMI => '#Images:search=',
            AWSResourceType::EBS => '#Volumes:search=',
            AWSResourceType::EC2 => '#Instances:search=',
            AWSResourceType::EIP => '#Addresses:search=',
            AWSResourceType::ELB => '#LoadBalancers:search=',
            AWSResourceType::SG => '#SecurityGroups:search=',
            AWSResourceType::SNAPSHOT => '#Snapshots:search=',
        };

        return $this->ec2PanelUrl . $section. $resource->getId();
    }
}
