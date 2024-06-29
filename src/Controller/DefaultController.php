<?php

namespace Majordome\Controller;

use Majordome\Entity\Violation;
use Majordome\Repository\RunRepository;
use Majordome\Repository\ViolationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    private RunRepository $runRepository;
    private ViolationRepository $violationRepository;

    public function __construct(RunRepository $runRepository, ViolationRepository $violationRepository)
    {
        $this->runRepository = $runRepository;
        $this->violationRepository = $violationRepository;
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $runs = $this->runRepository->findLastRuns(10);

        return $this->render('index.html.twig', ['runs' => $runs]);
    }

    /**
     * @param Violation[] $violations
     * @return array
     */
    private function groupViolationsByRule(array $violations): array
    {
        return array_reduce($violations, function (array $result, Violation $violation) {
            $rule = $violation->getRule();
            if (!array_key_exists($rule->getId(), $result)) {
                $result[$rule->getId()] = [
                    'rule' => $rule,
                    'violations' => []
                ];
            }
            $result[$rule->getId()]['violations'][] = $violation;
            return $result;
        }, []);
    }

    #[Route('/run/{id}', name: 'run_details')]
    public function runDetails(int $id): Response
    {
        $run = $this->runRepository->find($id);
        if (!$run) {
            throw new NotFoundHttpException(sprintf("run %s not found", $id));
        }

        $violations = $this->violationRepository->findByRun($run);
        $violationsByRule = $this->groupViolationsByRule($violations);

        return $this->render('run.html.twig', ['run' => $run, 'violationsByRules' => $violationsByRule]);
    }
}

;
