<?php

namespace Majordome\Resource;

class AWSResourceUrlGenerator implements ResourceUrlGeneratorInterface
{
    const WEB_CONSOLE_URL = 'https://console.aws.amazon.com';

    private $awsRegion;

    /**
     * @param string $awsRegion
     */
    public function __construct($awsRegion)
    {
        $this->awsRegion = $awsRegion;
    }

    /**
     * @param ResourceInterface $resource
     *
     * @return string
     */
    public function generateUrl(ResourceInterface $resource)
    {
        $ec2PanelUrl = self::WEB_CONSOLE_URL . '/ec2/home?region=' . $this->awsRegion;

        switch ($resource->getType()) {
            case AWSResourceType::AMI:
                $url = $ec2PanelUrl . '#Images:search=' . $resource->getId();
                break;
            case AWSResourceType::EBS:
                $url = $ec2PanelUrl . '#Volumes:search=' . $resource->getId();
                break;
            case AWSResourceType::EC2:
                $url = $ec2PanelUrl . '#Instances:search=' . $resource->getId();
                break;
            case AWSResourceType::EIP:
                $url = $ec2PanelUrl . '#Addresses:search=' . $resource->getId();
                break;
            case AWSResourceType::ELB:
                $url = $ec2PanelUrl . '#LoadBalancers:search=' . $resource->getId();
                break;
            case AWSResourceType::SG:
                $url = $ec2PanelUrl . '#SecurityGroups:search=' . $resource->getId();
                break;
            case AWSResourceType::SNAPSHOT:
                $url = $ec2PanelUrl . '#Snapshots:search=' . $resource->getId();
                break;
            default:
                $url = self::WEB_CONSOLE_URL;
                break;
        }

        return $url;
    }
}
