<?php

namespace Majordome\Tests\Resource;

use Majordome\Resource\AWSResourceType;
use Majordome\Resource\DefaultResource;
use Majordome\Resource\Url\AWSResourceUrlGenerator;
use Majordome\Resource\Url\ResourceUrlGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AWSResourceUrlGeneratorTest extends TestCase
{
    use ProphecyTrait;

    private ResourceUrlGenerator $generator;

    private static string $awsRegion = 'us-east-1';

    protected function setUp(): void
    {
        $this->generator = new AWSResourceUrlGenerator(self::$awsRegion);
    }

    #[DataProvider('resourcesProvider')]
    public function testGenerateResourceUrl(string $id, AWSResourceType $type)
    {
        $resource = new DefaultResource($id, $type->value, []);

        $result = $this->generator->generateUrl($resource);

        $this->assertIsString($result);
        $this->assertStringContainsString(AWSResourceUrlGenerator::WEB_CONSOLE_URL, $result);

        $this->assertStringContainsString(self::$awsRegion, $result);
        $this->assertStringContainsString($resource->getId(), $result);
    }

    public static function resourcesProvider(): array
    {
        return [
            ['i-AAAAAAAA', AWSResourceType::EC2],
            ['vol-BBBBBB', AWSResourceType::EBS],
            ['52.00.00.01', AWSResourceType::EIP],
            ['sg-CCCCCCCC', AWSResourceType::SG],
            ['XXXXXXXXXXX', AWSResourceType::ELB],
            ['iam.ec2.ami1', AWSResourceType::AMI],
            ['snap-DDDDDDD', AWSResourceType::SNAPSHOT]
        ];
    }
}
