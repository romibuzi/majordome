<?php

namespace Majordome\Command;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Majordome\Crawler\AWSCrawler;
use Majordome\Entity\Rule as RuleEntity;
use Majordome\Entity\Run;
use Majordome\Entity\Violation;
use Majordome\Repository\RuleRepository;
use Majordome\Resource\Resource;
use Majordome\Rule\AWS\DetachedEBSVolume;
use Majordome\Rule\AWS\ELBWithoutInstances;
use Majordome\Rule\AWS\UnusedAMI;
use Majordome\Rule\AWS\UnusedElasticIP;
use Majordome\Rule\AWS\UnusedSecurityGroup;
use Majordome\Rule\AWS\UnusedSnapshot;
use Majordome\Rule\Provider;
use Majordome\Rule\Rule;
use Majordome\Rule\SequentialRuleEngine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunAWSCommand extends Command
{
    private string $awsAccount;
    private string $awsRegion;
    private AWSCrawler $awsCrawler;
    private EntityManagerInterface $entityManager;
    private RuleRepository $ruleRepository;

    public function __construct(
        string                 $awsAccount,
        string                 $awsRegion,
        AWSCrawler             $awsCrawler,
        EntityManagerInterface $entityManager,
        RuleRepository         $ruleRepository
    ) {
        parent::__construct();

        $this->awsAccount = $awsAccount;
        $this->awsRegion = $awsRegion;
        $this->awsCrawler = $awsCrawler;
        $this->entityManager = $entityManager;
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('majordome:run-aws')
            ->setDescription('Run Majordome process on AWS');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Majordome is starting...</info>');

        $startTime = microtime(true);
        try {
            $this->runMajordome($output);
        } catch (\Exception $exception) {
            $output->writeln(sprintf('<error>Error happened: %s</error>', $exception->getMessage()));
            return Command::FAILURE;
        }
        $endTime = microtime(true);

        $output->writeln('<info>Majordome run completed !</info>');

        $output->writeln(sprintf(
            'Time: %4.2f seconds, Memory: %4.2f MB',
            $endTime - $startTime,
            memory_get_peak_usage(true) / (1024 * 1024)
        ));

        return Command::SUCCESS;
    }

    private function runMajordome(OutputInterface $output): void
    {
        $run = $this->createNewRun();

        $output->writeln('Listing AWS resources');
        $ebsResources = $this->awsCrawler->getEBSResources();
        $amiResources = $this->awsCrawler->getAMIResources($this->awsAccount);
        /** @var Resource[] $resources */
        $resources = array_merge_recursive(
            $ebsResources,
            $amiResources,
            $this->awsCrawler->getElasticIpResources(),
            $this->awsCrawler->getSecurityGroupResources(),
            $this->awsCrawler->getSnapshotResources($this->awsAccount),
            $this->awsCrawler->getELBResources()
        );

        $output->writeln('Instantiating rules');
        $rules = $this->createRules($ebsResources, $amiResources);
        $rulesEntities = $this->createRulesEntities($rules);

        $ruleEngine = new SequentialRuleEngine();
        $ruleEngine->addRules($rules);

        $output->writeln('Verifying each resource');
        foreach ($resources as $resource) {
            $isValid = $ruleEngine->isValid($resource);
            if (!$isValid) {
                $ruleEntity = $rulesEntities[$ruleEngine->getInvalidatedRule()::getName()];
                $violation = new Violation();
                $violation->setRun($run)
                    ->setRule($ruleEntity)
                    ->setResourceId($resource->getId())
                    ->setResourceType($resource->getType());
                $this->entityManager->persist($violation);
            }
        }

        $this->entityManager->flush();
    }

    private function createNewRun(): Run
    {
        $run = new Run();
        $run->setAccountId($this->awsAccount)
            ->setRegion($this->awsRegion)
            ->setProvider(Provider::AWS->value)
            ->setCreatedAt(new DateTimeImmutable());

        $this->entityManager->persist($run);
        $this->entityManager->flush();

        return $run;
    }

    private function createRules(array $ebsResources, array $amiResources): array
    {
        return [
            new DetachedEBSVolume(),
            new ELBWithoutInstances(),
            new UnusedAMI($this->awsCrawler->listEC2AMIs()),
            new UnusedElasticIP(),
            new UnusedSecurityGroup(
                array_unique(array_merge(
                    $this->awsCrawler->listEC2SecurityGroups(),
                    $this->awsCrawler->listElasticacheSecurityGroups(),
                    $this->awsCrawler->listELBSecurityGroups(),
                    $this->awsCrawler->listRdsSecurityGroups()
                ))
            ),
            new UnusedSnapshot(
                $this->getResourcesIds($ebsResources),
                $this->getResourcesIds($amiResources),
            )
        ];
    }

    /**
     * @param Rule[] $rules
     * @return array
     */
    private function createRulesEntities(array $rules): array
    {
        $entities = [];
        foreach ($rules as $rule) {
            $entity = $this->ruleRepository->findByName($rule::getName());
            if ($entity === null) {
                $entity = new RuleEntity();
                $entity->setName($rule::getName());
                $entity->setDescription($rule::getDescription());

                $this->entityManager->persist($entity);
                $this->entityManager->flush();
            }

            $entities[$rule::getName()] = $entity;
        }

        return $entities;
    }

    /**
     * @param Resource[] $resources
     * @return array
     */
    private function getResourcesIds(array $resources): array
    {
        return array_map(function (Resource $resource) {
            return $resource->getId();
        }, $resources);
    }
}
