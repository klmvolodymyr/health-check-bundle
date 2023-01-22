<?php

namespace VolodymyrKlymniuk\HealthCheckBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Liip\MonitorBundle\Helper\ArrayReporter;
use Liip\MonitorBundle\Helper\RunnerManager;
use Liip\MonitorBundle\Runner;

/**
 * Class HealthController
 *
 * @Route(path="/health", name="health_")
 */
class HealthController
{
    /**
     * @var RunnerManager
     */
    private $runnerManager;

    /**
     * @param RunnerManager $runnerManager
     */
    public function __construct(RunnerManager $runnerManager)
    {
        $this->runnerManager = $runnerManager;
    }

    /**
     * @Route(path="/check-all", name="check-all")
     *
     * @return JsonResponse
     */
    public function listAllAction()
    {
        $allChecks = [];

        foreach ($this->runnerManager->getRunners() as $group => $runner) {
            $reporter = new ArrayReporter();
            $runner->addReporter($reporter);
            $runner->run();
            $allChecks[$group][] = $reporter->getResults();
        }

        return new JsonResponse($allChecks);
    }

    /**
     * @Route(path="/check", name="check")
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws \RuntimeException
     */
    public function runAllChecksAction(Request $request): JsonResponse
    {
        $report = $this->runTests($request);

        return new JsonResponse(array(
            'checks' => $report->getResults(),
            'globalStatus' => $report->getGlobalStatus(),
        ));
    }

    /**
     * @Route(path="/status", name="status")
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function runAllChecksHttpStatusAction(Request $request): Response
    {
        $report = $this->runTests($request);

        return new Response('', ($report->getGlobalStatus() === ArrayReporter::STATUS_OK ? Response::HTTP_OK : Response::HTTP_BAD_GATEWAY));
    }

    /**
     * @param Request     $request
     * @param string|null $checkId
     *
     * @return ArrayReporter
     *
     * @throws \RuntimeException
     */
    protected function runTests(Request $request, $checkId = null): ArrayReporter
    {
        $reporter = new ArrayReporter();
        $runner = $this->getRunner($request);
        $runner->addReporter($reporter);
        $runner->run($checkId);

        return $reporter;
    }

    /**
     * @param Request $request
     *
     * @return Runner
     *
     * @throws \RuntimeException
     */
    private function getRunner(Request $request): Runner
    {
        $group = $this->getGroup($request);
        $runner = $this->runnerManager->getRunner($group);

        if ($runner) {
            return $runner;
        }

        throw new \RuntimeException(sprintf('Unknown check group "%s"', $group));
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    private function getGroup(Request $request): string
    {
        return $request->query->get('group') ?: $this->runnerManager->getDefaultGroup();
    }
}