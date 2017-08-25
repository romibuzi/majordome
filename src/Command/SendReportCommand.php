<?php

namespace Majordome\Command;

use Majordome\Manager\RunManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendReportCommand extends Command
{
    /** @var RunManager */
    private $manager;
    /** @var \Twig_Environment */
    private $twig;
    /** @var \Swift_Mailer */
    private $mailer;
    /** @var string */
    private $fromAddress;

    public function __construct(\Silex\Application $application)
    {
        parent::__construct();

        $this->manager = $application['run.manager'];
        $this->twig    = $application['twig'];
        $this->mailer  = $application['mailer'];
        $this->fromAddress = $application['report.sender_adress'];
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('majordome:send-report')
            ->setDescription('Send report of the last run by email')
            ->addArgument(
                'emails',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Who do you want to send the report (separate multiple emails with a space)'
            )
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Sending report...</info>');

        $lastRun = $this->manager->getLastRuns(1);
        list($run, $violationsByRule) = $this->manager->getRunDetails((int)$lastRun['id']);

        $header = $this->twig->render('report_header.twig');
        $body   = $this->twig->render('run_details.twig', ['run' => $run, 'violationsByRule' => $violationsByRule]);

        $message = \Swift_Message::newInstance(sprintf('Majordome Report', $lastRun['id']))
            ->setFrom([$this->fromAddress => 'Majordome'])
            ->setTo($input->getArgument('emails'))
            ->setBody($header . $body, 'text/html');

        try {
            $this->mailer->send($message);
            $output->writeln('<info>Report sent</info>');
        } catch (\Swift_SwiftException $e) {
            $output->writeln("<error>Could not send the report, following error happened : {$e->getMessage()}</error>");
        }
    }
}
