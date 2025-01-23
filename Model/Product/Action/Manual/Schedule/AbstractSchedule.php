<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Manual\Schedule;

use M2E\OnBuy\Model\Product\Action\Manual\Result;

abstract class AbstractSchedule extends \M2E\OnBuy\Model\Product\Action\Manual\AbstractManual
{
    private \M2E\OnBuy\Model\ScheduledAction\CreateService $scheduledActionCreateService;

    public function __construct(
        \M2E\OnBuy\Model\ScheduledAction\CreateService $scheduledActionCreateService,
        \M2E\OnBuy\Model\Product\ActionCalculator $calculator,
        \M2E\OnBuy\Model\Listing\LogService $listingLogService
    ) {
        parent::__construct($calculator, $listingLogService);
        $this->scheduledActionCreateService = $scheduledActionCreateService;
    }

    protected function processAction(array $actions, array $params): Result
    {
        foreach ($actions as $action) {
            $this->createScheduleAction(
                $action,
                $params,
                $this->scheduledActionCreateService,
            );
        }

        return Result::createSuccess($this->getLogActionId());
    }

    protected function createScheduleAction(
        \M2E\OnBuy\Model\Product\Action $action,
        array $params,
        \M2E\OnBuy\Model\ScheduledAction\CreateService $createService
    ): void {
        $scheduledActionParams = [
            'params' => $params,
        ];

        $createService->create(
            $action->getProduct(),
            $this->getAction(),
            \M2E\OnBuy\Model\Product::STATUS_CHANGER_USER,
            $scheduledActionParams,
            $action->getConfigurator()->getAllowedDataTypes(),
            true,
            $action->getConfigurator()
        );
    }
}
