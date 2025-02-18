<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Product\Action;

class ActionService
{
    private \M2E\OnBuy\Model\Listing\LogService $listingLogService;
    private \M2E\OnBuy\Model\Product\Action\Manual\Realtime\ListAction $realtimeListAction;
    private \M2E\OnBuy\Model\Product\Action\Manual\Schedule\ListAction $scheduledListAction;
    private \M2E\OnBuy\Model\Product\Action\Manual\Realtime\ReviseAction $realtimeReviseAction;
    private \M2E\OnBuy\Model\Product\Action\Manual\Schedule\ReviseAction $scheduledReviseAction;
    private \M2E\OnBuy\Model\Product\Action\Manual\Realtime\RelistAction $realtimeRelistAction;
    private \M2E\OnBuy\Model\Product\Action\Manual\Schedule\RelistAction $scheduledRelistAction;
    private \M2E\OnBuy\Model\Product\Action\Manual\Realtime\StopAction $realtimeStopAction;
    private \M2E\OnBuy\Model\Product\Action\Manual\Schedule\StopAction $scheduledStopAction;
    private \M2E\OnBuy\Model\Product\Action\Manual\Realtime\StopAndRemoveAction $realtimeStopAndRemoveAction;
    private \M2E\OnBuy\Model\Product\Action\Manual\Schedule\StopAndRemoveAction $scheduledStopAndRemoveAction;

    public function __construct(
        \M2E\OnBuy\Model\Listing\LogService                                 $listingLogService,
        \M2E\OnBuy\Model\Product\Action\Manual\Realtime\ListAction          $realtimeListAction,
        \M2E\OnBuy\Model\Product\Action\Manual\Schedule\ListAction          $scheduledListAction,
        \M2E\OnBuy\Model\Product\Action\Manual\Realtime\ReviseAction        $realtimeReviseAction,
        \M2E\OnBuy\Model\Product\Action\Manual\Schedule\ReviseAction        $scheduledReviseAction,
        \M2E\OnBuy\Model\Product\Action\Manual\Realtime\RelistAction        $realtimeRelistAction,
        \M2E\OnBuy\Model\Product\Action\Manual\Schedule\RelistAction        $scheduledRelistAction,
        \M2E\OnBuy\Model\Product\Action\Manual\Realtime\StopAction          $realtimeStopAction,
        \M2E\OnBuy\Model\Product\Action\Manual\Schedule\StopAction          $scheduledStopAction,
        \M2E\OnBuy\Model\Product\Action\Manual\Realtime\StopAndRemoveAction $realtimeStopAndRemoveAction,
        \M2E\OnBuy\Model\Product\Action\Manual\Schedule\StopAndRemoveAction $scheduledStopAndRemoveAction
    ) {
        $this->listingLogService = $listingLogService;
        $this->realtimeListAction = $realtimeListAction;
        $this->scheduledListAction = $scheduledListAction;
        $this->realtimeReviseAction = $realtimeReviseAction;
        $this->scheduledReviseAction = $scheduledReviseAction;
        $this->realtimeRelistAction = $realtimeRelistAction;
        $this->scheduledRelistAction = $scheduledRelistAction;
        $this->realtimeStopAction = $realtimeStopAction;
        $this->scheduledStopAction = $scheduledStopAction;
        $this->realtimeStopAndRemoveAction = $realtimeStopAndRemoveAction;
        $this->scheduledStopAndRemoveAction = $scheduledStopAndRemoveAction;
    }

    // ----------------------------------------

    public function runList(array $products): array
    {
        return $this->processRealtime($products, $this->realtimeListAction, []);
    }

    public function scheduleList(array $products): array
    {
        return $this->createScheduleAction($products, $this->scheduledListAction, []);
    }

    public function runRevise(array $products): array
    {
        return $this->processRealtime($products, $this->realtimeReviseAction, []);
    }

    public function scheduleRevise(array $products): array
    {
        return $this->createScheduleAction($products, $this->scheduledReviseAction, []);
    }

    public function runRelist(array $products): array
    {
        return $this->processRealtime($products, $this->realtimeRelistAction, []);
    }

    public function scheduleRelist(array $products): array
    {
        return $this->createScheduleAction($products, $this->scheduledRelistAction, []);
    }

    public function runStop(array $products): array
    {
        return $this->processRealtime($products, $this->realtimeStopAction, []);
    }

    public function scheduleStop(array $products): array
    {
        return $this->createScheduleAction($products, $this->scheduledStopAction, []);
    }

    public function runStopAndRemove(array $products): array
    {
        return $this->processRealtime($products, $this->realtimeStopAndRemoveAction, []);
    }

    public function scheduleStopAndRemove(array $products): array
    {
        return $this->createScheduleAction($products, $this->scheduledStopAndRemoveAction, []);
    }

    /**
     * @param \M2E\OnBuy\Model\Product[] $products
     * @param \M2E\OnBuy\Model\Product\Action\Manual\Realtime\AbstractRealtime $processor
     * @param array $params
     *
     * @return array
     */
    private function processRealtime(
        array $products,
        \M2E\OnBuy\Model\Product\Action\Manual\Realtime\AbstractRealtime $processor,
        array $params
    ): array {
        $logsActionId = $this->listingLogService->getNextActionId();
        if (empty($products)) {
            return ['result' => 'error', 'action_id' => $logsActionId];
        }

        $result = $processor->process($products, $params, $logsActionId);

        if ($result->isError()) {
            return ['result' => 'error', 'action_id' => $logsActionId];
        }

        if ($result->isWarning()) {
            return ['result' => 'warning', 'action_id' => $logsActionId];
        }

        return ['result' => 'success', 'action_id' => $logsActionId];
    }

    /**
     * @param \M2E\OnBuy\Model\Product[] $products
     * @param \M2E\OnBuy\Model\Product\Action\Manual\Schedule\AbstractSchedule $processor
     * @param array $params
     *
     * @return array
     */
    private function createScheduleAction(
        array                                                            $products,
        \M2E\OnBuy\Model\Product\Action\Manual\Schedule\AbstractSchedule $processor,
        array                                                            $params
    ): array {
        $logsActionId = $this->listingLogService->getNextActionId();
        if (empty($products)) {
            return ['result' => 'error', 'action_id' => $logsActionId];
        }

        $processor->process($products, $params, $logsActionId);

        return ['result' => 'success', 'action_id' => $logsActionId];
    }
}
