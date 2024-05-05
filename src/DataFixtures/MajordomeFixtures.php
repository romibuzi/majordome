<?php

namespace Majordome\DataFixtures;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Majordome\Entity\Rule;
use Majordome\Entity\Run;
use Majordome\Entity\Violation;
use Majordome\Resource\AWSResourceType;
use Majordome\Rule\AWS\DetachedEBSVolume;
use Majordome\Rule\AWS\UnusedSecurityGroup;
use Majordome\Rule\Provider;

class MajordomeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $firstRun = new Run();
        $firstRun->setAccountId('272962896599');
        $firstRun->setProvider(Provider::AWS->value);
        $firstRun->setRegion('eu-west-1');
        $firstRun->setCreatedAt(DateTimeImmutable::createFromFormat(
            DateTimeInterface::ATOM,
            '2024-03-02T15:19:00+00:00'
        ));

        $secondRun = new Run();
        $secondRun->setAccountId('272962896599');
        $secondRun->setProvider(Provider::AWS->value);
        $secondRun->setRegion('eu-west-1');
        $secondRun->setCreatedAt(DateTimeImmutable::createFromFormat(
            DateTimeInterface::ATOM,
            '2024-03-04T11:03:00+00:00'
        ));

        $manager->persist($firstRun);
        $manager->persist($secondRun);

        $unusedSgRule = new Rule();
        $unusedSgRule->setName(UnusedSecurityGroup::getName());
        $unusedSgRule->setDescription(UnusedSecurityGroup::getDescription());

        $detachedEbsRule = new Rule();
        $detachedEbsRule->setName(DetachedEBSVolume::getName());
        $detachedEbsRule->setDescription(DetachedEBSVolume::getDescription());

        $manager->persist($unusedSgRule);
        $manager->persist($detachedEbsRule);

        $firstRunFirstViolation = new Violation();
        $firstRunFirstViolation->setRun($firstRun);
        $firstRunFirstViolation->setRule($unusedSgRule);
        $firstRunFirstViolation->setResourceType(AWSResourceType::SG->value);
        $firstRunFirstViolation->setResourceId('sg-d725dcbx');

        $firstRunSecondViolation = new Violation();
        $firstRunSecondViolation->setRun($firstRun);
        $firstRunSecondViolation->setRule($unusedSgRule);
        $firstRunSecondViolation->setResourceType(AWSResourceType::SG->value);
        $firstRunSecondViolation->setResourceId('sg-b620a5df');

        $firstRunThirdViolation = new Violation();
        $firstRunThirdViolation->setRun($firstRun);
        $firstRunThirdViolation->setRule($detachedEbsRule);
        $firstRunThirdViolation->setResourceType(AWSResourceType::EBS->value);
        $firstRunThirdViolation->setResourceId('vol-8e3fbx57');

        $firstRunFourthViolation = new Violation();
        $firstRunFourthViolation->setRun($firstRun);
        $firstRunFourthViolation->setRule($detachedEbsRule);
        $firstRunFourthViolation->setResourceType(AWSResourceType::EBS->value);
        $firstRunFourthViolation->setResourceId('vol-05bcbe339e163c9c7');

        $secondRunFirstViolation = new Violation();
        $secondRunFirstViolation->setRun($secondRun);
        $secondRunFirstViolation->setRule($unusedSgRule);
        $secondRunFirstViolation->setResourceType(AWSResourceType::SG->value);
        $secondRunFirstViolation->setResourceId('sg-fd965794');

        $manager->persist($firstRunFirstViolation);
        $manager->persist($firstRunSecondViolation);
        $manager->persist($firstRunThirdViolation);
        $manager->persist($firstRunFourthViolation);
        $manager->persist($secondRunFirstViolation);

        $manager->flush();
    }
}
