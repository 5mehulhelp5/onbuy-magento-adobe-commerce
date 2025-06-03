<?php

namespace M2E\OnBuy\Model\ScheduledAction;

use M2E\OnBuy\Model\Product\Action\Configurator;
use M2E\OnBuy\Model\ResourceModel\ScheduledAction\Collection as ScheduledActionCollection;
use M2E\OnBuy\Model\ResourceModel\ScheduledAction\CollectionFactory as ScheduledActionCollectionFactory;

class Processor
{
    private const LIST_PRIORITY = 25;
    private const REVISE_QTY_PRIORITY = 500;
    private const REVISE_PRICE_PRIORITY = 250;
    private const REVISE_SHIPPING_PRIORITY = 50;
    private const REVISE_DETAILS_PRIORITY = 50;
    private const RELIST_PRIORITY = 125;
    private const STOP_PRIORITY = 1000;

    private \M2E\OnBuy\Model\Config\Manager $config;
    private \Magento\Framework\App\ResourceConnection $resourceConnection;
    private \M2E\OnBuy\Helper\Module\Exception $exceptionHelper;
    private \M2E\OnBuy\Model\Product\Action\Dispatcher $actionDispatcher;
    /** @var \M2E\OnBuy\Model\ScheduledAction\Repository */
    private Repository $scheduledActionRepository;

    public function __construct(
        \M2E\OnBuy\Model\ScheduledAction\Repository $scheduledActionRepository,
        \M2E\OnBuy\Model\Config\Manager $config,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \M2E\OnBuy\Helper\Module\Exception $exceptionHelper,
        \M2E\OnBuy\Model\Product\Action\Dispatcher $actionDispatcher
    ) {
        $this->config = $config;
        $this->resourceConnection = $resourceConnection;
        $this->exceptionHelper = $exceptionHelper;
        $this->actionDispatcher = $actionDispatcher;
        $this->scheduledActionRepository = $scheduledActionRepository;
    }

    public function process(): void
    {
        $limit = $this->calculateActionsCountLimit();
        if ($limit === 0) {
            return;
        }

        $scheduledActions = $this->getScheduledActionsForProcessing($limit);
        if (empty($scheduledActions)) {
            return;
        }

        foreach ($scheduledActions as $scheduledAction) {
            try {
                $listingProduct = $scheduledAction->getListingProduct();
                $additionalData = $scheduledAction->getAdditionalData();
                $statusChanger = $scheduledAction->getStatusChanger();
            } catch (\M2E\OnBuy\Model\Exception\Logic $e) {
                $this->exceptionHelper->process($e);

                $this->scheduledActionRepository->remove($scheduledAction);

                continue;
            }

            $params = $additionalData['params'] ?? [];

            $listingProduct->setActionConfigurator($scheduledAction->getConfigurator());

            switch ($scheduledAction->getActionType()) {
                case \M2E\OnBuy\Model\Product::ACTION_LIST:
                    $this->actionDispatcher->processList($listingProduct, $params, $statusChanger);
                    break;
                case \M2E\OnBuy\Model\Product::ACTION_REVISE:
                    $this->actionDispatcher->processRevise($listingProduct, $params, $statusChanger);
                    break;
                case \M2E\OnBuy\Model\Product::ACTION_STOP:
                    $this->actionDispatcher->processStop($listingProduct, $params, $statusChanger);
                    break;
                case \M2E\OnBuy\Model\Product::ACTION_DELETE:
                    $this->actionDispatcher->processDelete($listingProduct, $params, $statusChanger);
                    break;
                case \M2E\OnBuy\Model\Product::ACTION_RELIST:
                    $this->actionDispatcher->processRelist($listingProduct, $params, $statusChanger);
                    break;
                default:
                    throw new \DomainException("Unknown action '{$scheduledAction->getActionType()}'");
            }

            $this->scheduledActionRepository->remove($scheduledAction);
        }
    }

    private function calculateActionsCountLimit(): int
    {
        $maxAllowedActionsCount = (int)$this->config->get(
            '/listing/product/scheduled_actions/',
            'max_prepared_actions_count'
        );

        if ($maxAllowedActionsCount <= 0) {
            return 0;
        }

        return $maxAllowedActionsCount;
    }

    /**
     * @return \M2E\OnBuy\Model\ScheduledAction[]
     */
    private function getScheduledActionsForProcessing(int $limit): array
    {
        $connection = $this->resourceConnection->getConnection();

        $unionSelect = $connection->select()->union([
            $this->getListScheduledActionsPreparedCollection()->getSelect(),
            $this->getRevisePriceScheduledActionsPreparedCollection()->getSelect(),
            $this->getReviseQtyScheduledActionsPreparedCollection()->getSelect(),
            $this->getReviseShippingScheduledActionsPreparedCollection()->getSelect(),
            $this->getReviseDetailsScheduledActionsPreparedCollection()->getSelect(),
            $this->getRelistScheduledActionsPreparedCollection()->getSelect(),
            $this->getStopScheduledActionsPreparedCollection()->getSelect(),
            $this->getDeleteScheduledActionsPreparedCollection()->getSelect(),
        ]);

        $unionSelect->order(['coefficient DESC']);
        $unionSelect->order(['create_date ASC']);

        $unionSelect->distinct(true);
        $unionSelect->limit($limit);

        $scheduledActionsData = $unionSelect->query()->fetchAll();
        if (empty($scheduledActionsData)) {
            return [];
        }

        $scheduledActionsIds = [];
        foreach ($scheduledActionsData as $scheduledActionData) {
            $scheduledActionsIds[] = $scheduledActionData['id'];
        }

        return $this->scheduledActionRepository->getByIds($scheduledActionsIds);
    }

    private function getListScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionRepository->createCollectionForFindByActionType(
            self::LIST_PRIORITY,
            \M2E\OnBuy\Model\Product::ACTION_LIST
        );

        return $collection;
    }

    private function getReviseQtyScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionRepository->createCollectionForFindByActionType(
            self::REVISE_QTY_PRIORITY,
            \M2E\OnBuy\Model\Product::ACTION_REVISE
        );
        $collection->addTagFilter('qty');

        return $collection;
    }

    private function getRevisePriceScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionRepository->createCollectionForFindByActionType(
            self::REVISE_PRICE_PRIORITY,
            \M2E\OnBuy\Model\Product::ACTION_REVISE
        );
        $collection->addTagFilter('price');

        return $collection;
    }

    private function getReviseShippingScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionRepository->createCollectionForFindByActionType(
            self::REVISE_SHIPPING_PRIORITY,
            \M2E\OnBuy\Model\Product::ACTION_REVISE
        );
        $collection->addTagFilter('shipping');

        return $collection;
    }

    private function getReviseDetailsScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionRepository->createCollectionForFindByActionType(
            self::REVISE_DETAILS_PRIORITY,
            \M2E\OnBuy\Model\Product::ACTION_REVISE
        );
        $collection->addTagFilter('details');

        return $collection;
    }

    private function getRelistScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionRepository->createCollectionForFindByActionType(
            self::RELIST_PRIORITY,
            \M2E\OnBuy\Model\Product::ACTION_RELIST
        );

        return $collection;
    }

    private function getStopScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        return $this->scheduledActionRepository->createCollectionForFindByActionType(
            self::STOP_PRIORITY,
            \M2E\OnBuy\Model\Product::ACTION_STOP
        );
    }

    private function getDeleteScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        return $this->scheduledActionRepository->createCollectionForFindByActionType(
            self::STOP_PRIORITY,
            \M2E\OnBuy\Model\Product::ACTION_DELETE
        );
    }
}
