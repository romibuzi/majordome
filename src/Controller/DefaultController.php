<?php

namespace Majordome\Controller;

use Majordome\Manager\RunManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController
{
    private $manager;
    private $twig;
    private $logger;

    public function __construct(RunManager $manager, \Twig_Environment $twig, LoggerInterface $logger = null)
    {
        $this->manager    = $manager;
        $this->twig       = $twig;
        $this->logger     = $logger;
    }

    /**
     * Main Action, list all differents Majordome runs
     *
     * @return string : the template
     */
    public function indexAction()
    {
        $runs = $this->manager->getLastRuns(10);

        return $this->twig->render('index.twig', ['runs' => $runs]);
    }

    /**
     * @param string $id
     *
     * @return string : the template
     */
    public function runDetailsAction($id)
    {
        list($run, $violationsByRule) = $this->manager->getRunDetails((int)$id);

        if (!$run) {
            throw new NotFoundHttpException(sprintf("run %s wasn't found", $id));
        }

        return $this->twig->render('run.twig', ['run' => $run, 'violationsByRule' => $violationsByRule]);
    }
}
