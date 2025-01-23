<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Cron\Task\Product;

class InspectDirectChangesTask extends \M2E\OnBuy\Model\Cron\AbstractTask
{
    public const NICK = 'product/inspect_direct_changes';

    private \M2E\OnBuy\Model\Product\InspectDirectChanges $inspectDirectChanges;
    private \M2E\OnBuy\Model\Product\InspectDirectChanges\Config $config;

    public function __construct(
        \M2E\OnBuy\Model\Product\InspectDirectChanges\Config $config,
        \M2E\OnBuy\Model\Product\InspectDirectChanges $inspectDirectChanges,
        \M2E\OnBuy\Model\Cron\Manager $cronManager,
        \M2E\OnBuy\Model\Synchronization\LogService $syncLogger,
        \M2E\OnBuy\Helper\Data $helperData,
        \Magento\Framework\Event\Manager $eventManager,
        \M2E\OnBuy\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\OnBuy\Model\Cron\TaskRepository $taskRepo,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct(
            $cronManager,
            $syncLogger,
            $helperData,
            $eventManager,
            $activeRecordFactory,
            $taskRepo,
            $resource,
        );

        $this->config = $config;
        $this->inspectDirectChanges = $inspectDirectChanges;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    public function isPossibleToRun()
    {
        if (
            !$this->config->isEnableProductInspectorMode()
        ) {
            return false;
        }

        return parent::isPossibleToRun();
    }

    protected function performActions(): void
    {
        $this->inspectDirectChanges->process();
    }
}
