<?php

namespace Majordome\Tests\Manager;

use Majordome\Manager\RunManager;
use Majordome\Resource\AWSResourceType;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class RunManagerTest extends TestCase
{
    use ProphecyTrait;

    /** @var \Doctrine\DBAL\Connection|ObjectProphecy */
    private $connection;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->connection = $this->prophesize('Doctrine\DBAL\Connection');
    }

    public function testGetLastRuns()
    {
        $runs = [
            ['id' => 1, 'createdAt' => '2016-09-21 10:00:00'],
            ['id' => 2, 'createdAt' => '2016-09-22 14:25:00'],
        ];

        $this->connection
            ->fetchAllAssociative(Argument::containingString('SELECT r.*'))
            ->WillReturn($runs)
            ->shouldBeCalled();

        $manager = new RunManager($this->connection->reveal());
        $result  = $manager->getLastRuns(10);

        $this->assertSame($runs, $result);
    }

    public function testGetLastRun()
    {
        $run = ['id' => 1, 'createdAt' => '2016-09-21 10:00:00'];

        $this->connection
            ->fetchAssociative(Argument::containingString('SELECT r.*'))
            ->WillReturn($run)
            ->shouldBeCalled();

        $manager = new RunManager($this->connection->reveal());
        $result  = $manager->getLastRuns(1);

        $this->assertSame($run, $result);
    }

    public function testGetRunDetails()
    {
        $runId = 1;

        $runExpected = ['id' => $runId, 'createdAt' => '2016-09-21 10:00:00'];
        $violations = [
            [
                "resource_id" => "vol-1111111111",
                "resource_type" => AWSResourceType::EBS,
                "rule_name" => "DetachedEBSVolume",
                "rule_description" => "",
                "rule_id" => 1
            ],
            [
                "resource_id" => "sg-222222222",
                "resource_type" => AWSResourceType::SG,
                "rule_name" => "UnusedSecurityGroup",
                "rule_description" => "",
                "rule_id" => 2
            ]
        ];

        $this->connection->fetchAssociative(
            Argument::containingString('SELECT * FROM runs WHERE id = ?'),
            Argument::containing($runId),
            Argument::containing(\PDO::PARAM_INT)
        )->WillReturn($runExpected)->shouldBeCalled();

        $this->connection->fetchAllAssociative(
            Argument::type('string'),
            Argument::containing($runId),
            Argument::containing(\PDO::PARAM_INT)
        )->WillReturn($violations)->shouldBeCalled();

        $manager = new RunManager($this->connection->reveal());
        list($run, $violationsByRule) = $manager->getRunDetails($runId);

        $this->assertSame($runExpected, $run);

        $this->assertSame(2, count($violationsByRule));
        foreach ($violationsByRule as $rule) {
            $this->assertSame(1, count($rule['violations']));
        }
    }

    public function testGetRunDetailsWillReturnFalseWhenRunNotFound()
    {
        $runId = 1563;

        $this->connection->fetchAssociative(
            Argument::containingString('SELECT * FROM runs WHERE id = ?'),
            Argument::containing($runId),
            Argument::containing(\PDO::PARAM_INT)
        )->WillReturn(false)->shouldBeCalled();

        $manager = new RunManager($this->connection->reveal());
        $result = $manager->getRunDetails($runId);

        $this->assertSame([false, false], $result);
    }
}
