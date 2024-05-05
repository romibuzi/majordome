<?php

namespace Majordome\Resource;

enum AWSResourceType: string
{
    case AMI = 'ami';
    case EBS = 'ebs';
    case EC2 = 'ec2';
    case EIP = 'elastic-ip';
    case ELB = 'elb';
    case SG = 'security-group';
    case SNAPSHOT = 'snapshot';
}
