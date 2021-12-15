<?php

namespace Majordome\Tests\Resource;

use Majordome\Resource\AWSResourceType;
use Majordome\Resource\AWSResourceUrlGenerator;
use Majordome\Resource\ResourceUrlGeneratorInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AWSResourceUrlGeneratorTest extends TestCase
{
    use ProphecyTrait;

    /** @var ResourceUrlGeneratorInterface */
    private $generator;

    private static $awsRegion = 'us-east-1';

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->generator = new AWSResourceUrlGenerator(self::$awsRegion);
    }

    /**
     * @dataProvider resourcesProvider
     *
     * @param string $id
     * @param string $type
     */
    public function testGenerateResourceUrl($id, $type)
    {
        $resource = $this->prophesize();
        $resource->willImplement('Majordome\Resource\ResourceInterface');

        $resource->getId()->willReturn($id);
        $resource->getType()->willReturn($type)->shouldBeCalled();

        $result = $this->generator->generateUrl($resource->reveal());

        $this->assertIsString($result);
        $this->assertStringContainsString(AWSResourceUrlGenerator::WEB_CONSOLE_URL, $result);

        if ($type !== 'unknown') {
            $this->assertStringContainsString(self::$awsRegion, $result);
            $this->assertStringContainsString($id, $result);

            $resource->getId()->shouldHaveBeenCalled();
        }
    }

    public function resourcesProvider()
    {
        return [
            ['i-AAAAAAAA', AWSResourceType::EC2],
            ['vol-BBBBBB', AWSResourceType::EBS],
            ['52.00.00.01', AWSResourceType::EIP],
            ['sg-CCCCCCCC', AWSResourceType::SG],
            ['XXXXXXXXXXX', AWSResourceType::ELB],
            ['iam.ec2.ami1', AWSResourceType::AMI],
            ['snap-DDDDDDD', AWSResourceType::SNAPSHOT],
            ['unknown', 'unknown'],
        ];
    }
}
