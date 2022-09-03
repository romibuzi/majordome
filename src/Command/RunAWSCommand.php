<?php

namespace Majordome\Command;

use Majordome\Crawler\AWSCrawler;
use Majordome\Resource\ResourceInterface;
use Majordome\Rule\AWS\DetachedEBSVolume;
use Majordome\Rule\AWS\ELBWithoutInstances;
use Majordome\Rule\AWS\UnusedAMI;
use Majordome\Rule\AWS\UnusedElasticIP;
use Majordome\Rule\AWS\UnusedSecurityGroup;
use Majordome\Rule\AWS\UnusedSnapchot;
use Majordome\Rule\BasicRuleEngine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunAWSCommand extends Command
{
    private $application;

    public function __construct(\Silex\Application $application)
    {
        parent::__construct();

        $this->application = $application;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('majordome:run-aws')
            ->setDescription('Run Majordome process on AWS')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Majordome is starting...</info>');

        $startTime = microtime(true);
        $this->runMajordome($output);
        $endTime = microtime(true);

        $output->writeln('<info>Majordome run completed !</info>');

        $output->writeln(sprintf(
            'Time: %4.2f seconds, Memory: %4.2f MB',
            $endTime - $startTime,
            memory_get_peak_usage(true) / (1024 * 1024)
        ));
    }

    private function runMajordome(OutputInterface $output)
    {
        /** @var \Doctrine\DBAL\Connection $db */
        $db = $this->application['db'];

        $output->writeln('Creating a new run');
        $db->insert('runs', [
            'createdAt' => (new \DateTime())->format('Y-m-d H:i:s')
        ]);
        $runId = $db->lastInsertId();

        $output->writeln('Listing resources on AWS');
        $awsCrawler = new AWSCrawler($this->application['aws.sdk']);

        $ebsResources = $awsCrawler->getEBSResources();
        $ebsVolumesIds = array_map(function ($ebsResource) {
            return $ebsResource->getId();
        }, $ebsResources);

        $AMIResources = $awsCrawler->getAMIResources($this->application['aws.accountId']);
        $AMIResourcesIds = array_map(function ($AMIResources) {
            return $AMIResources->getId();
        }, $AMIResources);

        /** @var ResourceInterface[] $resources */
        $resources = array_merge_recursive(
            $ebsResources,
            $AMIResources,
            $awsCrawler->getElasticIpResources(),
            $awsCrawler->getSecurityGroupResources(),
            $awsCrawler->getSnapshotResources($this->application['aws.accountId']),
            $awsCrawler->getELBResources()
        );

        $ruleEngine = new BasicRuleEngine();
        $rulesConfig = $this->application['aws.rules'];

        if ($rulesConfig['DetachedEBS']) {
            $ruleEngine->addRule(new DetachedEBSVolume());
        }
        if ($rulesConfig['ELBWithoutMultipleInstances']) {
            $ruleEngine->addRule(new ELBWithoutInstances());
        }
        if ($rulesConfig['UnusedAMI']) {
            $ruleEngine->addRule(new UnusedAMI($awsCrawler->listEC2AMIs()));
        }
        if ($rulesConfig['UnusedElasticIP']) {
            $ruleEngine->addRule(new UnusedElasticIP());
        }
        if ($rulesConfig['UnusedSecurityGroup']) {
            $ruleEngine->addRule(new UnusedSecurityGroup(
                array_unique(array_merge(
                    $awsCrawler->listEC2SecurityGroups(),
                    $awsCrawler->listElasticacheSecurityGroups(),
                    $awsCrawler->listELBSecurityGroups(),
                    $awsCrawler->listRdsSecurityGroups()
                ))
            ));
        }
        if ($rulesConfig['UnusedSnapshot']) {
            $ruleEngine->addRule(new UnusedSnapchot($ebsVolumesIds, $AMIResourcesIds));
        }

        $rules = [];
        // Push rules in database if not already done during a previous run
        foreach ($ruleEngine->getRules() as $rule) {
            $ruleName = $rule->getName();
            $id = $db->fetchOne("SELECT id FROM rules WHERE name = ?", [$ruleName]);
            if (!$id) {
                $db->insert('rules', [
                    'name' => $ruleName,
                    'description' => $rule->getDescription()
                ]);
                $rules[$ruleName] = $db->lastInsertId();
            } else {
                $rules[$ruleName] = $id;
            }

            $output->writeln(sprintf('Rule `%s` is enabled', $ruleName));
        }

        $output->writeln('Verifying each resource');
        foreach ($resources as $resource) {
            $isValid = $ruleEngine->isValid($resource);

            if (!$isValid) {
                $db->insert('violations', [
                    'run_id'        => $runId,
                    'resource_id'   => $resource->getId(),
                    'resource_type' => $resource->getType(),
                    'rule_id'       => $rules[$ruleEngine->getInvalidatedRule()->getName()],
                ]);

                $this->application['logger']->info(sprintf(
                    '%s rule has identified %s resource as invalid',
                    $ruleEngine->getInvalidatedRule()->getName(),
                    $resource->getId()
                ), [
                    'rule_description' => $ruleEngine->getInvalidatedRule()->getDescription()
                ]);
            }
        }
    }
}
