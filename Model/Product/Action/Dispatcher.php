<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action;

use M2E\OnBuy\Model\Product\Action\Async\DefinitionsCollection as AsyncActions;

class Dispatcher
{
    private \M2E\OnBuy\Model\Tag\ListingProduct\Buffer $tagBuffer;
    private \M2E\OnBuy\Model\TagFactory $tagFactory;
    private \M2E\OnBuy\Helper\Module\Exception $exceptionHelper;
    private ProcessorAsyncFactory $processorAsyncFactory;
    private \M2E\OnBuy\Model\Listing\LogService $listingLogService;
    private ConfiguratorFactory $configuratorFactory;

    public function __construct(
        \M2E\OnBuy\Model\Tag\ListingProduct\Buffer $tagBuffer,
        \M2E\OnBuy\Model\TagFactory $tagFactory,
        \M2E\OnBuy\Helper\Module\Exception $exceptionHelper,
        ProcessorAsyncFactory $processorAsyncFactory,
        \M2E\OnBuy\Model\Listing\LogService $listingLogService,
        ConfiguratorFactory $configuratorFactory
    ) {
        $this->tagBuffer = $tagBuffer;
        $this->tagFactory = $tagFactory;
        $this->exceptionHelper = $exceptionHelper;
        $this->processorAsyncFactory = $processorAsyncFactory;
        $this->listingLogService = $listingLogService;
        $this->configuratorFactory = $configuratorFactory;
    }

    /**
     * @param \M2E\OnBuy\Model\Product $product
     * @param array $params
     * @param int $statusChanger
     *
     * @return \M2E\Core\Helper\Data::STATUS_SUCCESS | \M2E\Core\Helper\Data::STATUS_ERROR
     */
    public function processRevise(\M2E\OnBuy\Model\Product $product, array $params, int $statusChanger): int
    {
        $logsActionId = $this->getLogActionId($params);
        $params += ['logs_action_id' => $logsActionId];

        $this->removeTags($product);

        try {
            $processor = $this->processorAsyncFactory->createProcessStart(
                AsyncActions::ACTION_REVISE,
                $product,
                $this->getActionConfigurator($product),
                $statusChanger,
                $logsActionId,
                \M2E\OnBuy\Model\Listing\Log::ACTION_REVISE_PRODUCT,
                $params,
            );

            $result = $processor->process();
            if ($result === \M2E\Core\Helper\Data::STATUS_ERROR) {
                $this->tagBuffer->addTag($product, $this->tagFactory->createWithHasErrorCode());
                $this->tagBuffer->flush();
            }

            return $result;
        } catch (\Throwable $exception) {
            $this->logListingProductException(
                $product,
                $exception,
                \M2E\OnBuy\Model\Product::ACTION_REVISE,
                $statusChanger,
                $logsActionId
            );
            $this->exceptionHelper->process($exception);

            return \M2E\Core\Helper\Data::STATUS_ERROR;
        }
    }

    /**
     * @param \M2E\OnBuy\Model\Product $product
     * @param array $params
     * @param int $statusChanger
     *
     * @return \M2E\Core\Helper\Data::STATUS_SUCCESS | \M2E\Core\Helper\Data::STATUS_ERROR
     */
    public function processStop(\M2E\OnBuy\Model\Product $product, array $params, int $statusChanger): int
    {
        $logsActionId = $this->getLogActionId($params);
        $params += ['logs_action_id' => $logsActionId];

        $this->removeTags($product);

        try {
            $processor = $this->processorAsyncFactory->createProcessStart(
                AsyncActions::ACTION_STOP,
                $product,
                $this->getActionConfigurator($product),
                $statusChanger,
                $logsActionId,
                \M2E\OnBuy\Model\Listing\Log::ACTION_STOP_PRODUCT,
                $params,
            );

            $result = $processor->process();
            if ($result === \M2E\Core\Helper\Data::STATUS_ERROR) {
                $this->tagBuffer->addTag($product, $this->tagFactory->createWithHasErrorCode());
                $this->tagBuffer->flush();
            }

            return $result;
        } catch (\Throwable $exception) {
            $this->logListingProductException(
                $product,
                $exception,
                \M2E\OnBuy\Model\Product::ACTION_STOP,
                $statusChanger,
                $logsActionId
            );
            $this->exceptionHelper->process($exception);

            return \M2E\Core\Helper\Data::STATUS_ERROR;
        }
    }

    /**
     * @param \M2E\OnBuy\Model\Product $product
     * @param array $params
     * @param int $statusChanger
     *
     * @return \M2E\Core\Helper\Data::STATUS_SUCCESS | \M2E\Core\Helper\Data::STATUS_ERROR
     */
    public function processDelete(\M2E\OnBuy\Model\Product $product, array $params, int $statusChanger): int
    {
        $logsActionId = $this->getLogActionId($params);
        $params += ['logs_action_id' => $logsActionId];

        $this->removeTags($product);

        try {
            $processor = $this->processorAsyncFactory->createProcessStart(
                AsyncActions::ACTION_DELETE,
                $product,
                $this->getActionConfigurator($product),
                $statusChanger,
                $logsActionId,
                \M2E\OnBuy\Model\Listing\Log::ACTION_REMOVE_PRODUCT,
                $params,
            );

            $result = $processor->process();
            if ($result === \M2E\Core\Helper\Data::STATUS_ERROR) {
                $this->tagBuffer->addTag($product, $this->tagFactory->createWithHasErrorCode());
                $this->tagBuffer->flush();
            }

            return $result;
        } catch (\Throwable $exception) {
            $this->logListingProductException(
                $product,
                $exception,
                \M2E\OnBuy\Model\Product::ACTION_DELETE,
                $statusChanger,
                $logsActionId
            );
            $this->exceptionHelper->process($exception);

            return \M2E\Core\Helper\Data::STATUS_ERROR;
        }
    }

    /**
     * @param \M2E\OnBuy\Model\Product $product
     * @param array $params
     * @param int $statusChanger
     *
     * @return \M2E\Core\Helper\Data::STATUS_SUCCESS | \M2E\Core\Helper\Data::STATUS_ERROR
     */
    public function processRelist(\M2E\OnBuy\Model\Product $product, array $params, int $statusChanger): int
    {
        $logsActionId = $this->getLogActionId($params);
        $params += ['logs_action_id' => $logsActionId];

        $this->removeTags($product);

        try {
            $processor = $this->processorAsyncFactory->createProcessStart(
                AsyncActions::ACTION_RELIST,
                $product,
                $this->getActionConfigurator($product),
                $statusChanger,
                $logsActionId,
                \M2E\OnBuy\Model\Listing\Log::ACTION_RELIST_PRODUCT,
                $params,
            );

            $result = $processor->process();
            if ($result === \M2E\Core\Helper\Data::STATUS_ERROR) {
                $this->tagBuffer->addTag($product, $this->tagFactory->createWithHasErrorCode());
                $this->tagBuffer->flush();
            }

            return $result;
        } catch (\Throwable $exception) {
            $this->logListingProductException(
                $product,
                $exception,
                \M2E\OnBuy\Model\Product::ACTION_RELIST,
                $statusChanger,
                $logsActionId
            );
            $this->exceptionHelper->process($exception);

            return \M2E\Core\Helper\Data::STATUS_ERROR;
        }
    }

    // ----------------------------------------

    private function getLogActionId(array $params): int
    {
        if (!empty($params['logs_action_id'])) {
            return $params['logs_action_id'];
        }

        return $this->listingLogService->getNextActionId();
    }

    private function getActionConfigurator(\M2E\OnBuy\Model\Product $product): Configurator
    {
        if ($product->getActionConfigurator() === null) {
            $actionConfigurator = $this->configuratorFactory->create();
            $product->setActionConfigurator($actionConfigurator);
        }

        return $product->getActionConfigurator();
    }

    private function removeTags(\M2E\OnBuy\Model\Product $listingProduct): void
    {
        $this->tagBuffer->removeAllTags($listingProduct);
        $this->tagBuffer->flush();
    }

    private function logListingProductException(
        \M2E\OnBuy\Model\Product $listingProduct,
        \Throwable $exception,
        int $action,
        int $statusChanger,
        int $logActionId
    ): void {
        $action = $this->recognizeActionForLogging($action);
        $initiator = $this->recognizeInitiatorForLogging($statusChanger);

        $this->listingLogService->addProduct(
            $listingProduct,
            $initiator,
            $action,
            $logActionId,
            $exception->getMessage(),
            \M2E\OnBuy\Model\Log\AbstractModel::TYPE_ERROR,
        );
    }

    private function recognizeInitiatorForLogging(int $statusChanger): int
    {
        if ($statusChanger === \M2E\OnBuy\Model\Product::STATUS_CHANGER_UNKNOWN) {
            return \M2E\Core\Helper\Data::INITIATOR_UNKNOWN;
        }
        if ($statusChanger === \M2E\OnBuy\Model\Product::STATUS_CHANGER_USER) {
            return \M2E\Core\Helper\Data::INITIATOR_USER;
        }

        return \M2E\Core\Helper\Data::INITIATOR_EXTENSION;
    }

    private function recognizeActionForLogging(int $action): int
    {
        $logAction = \M2E\OnBuy\Model\Listing\Log::ACTION_UNKNOWN;

        switch ($action) {
            case \M2E\OnBuy\Model\Product::ACTION_LIST:
                $logAction = \M2E\OnBuy\Model\Listing\Log::ACTION_LIST_PRODUCT;
                break;
            case \M2E\OnBuy\Model\Product::ACTION_RELIST:
                $logAction = \M2E\OnBuy\Model\Listing\Log::ACTION_RELIST_PRODUCT;
                break;
            case \M2E\OnBuy\Model\Product::ACTION_REVISE:
                $logAction = \M2E\OnBuy\Model\Listing\Log::ACTION_REVISE_PRODUCT;
                break;
            case \M2E\OnBuy\Model\Product::ACTION_STOP:
                $logAction = \M2E\OnBuy\Model\Listing\Log::ACTION_STOP_PRODUCT;
                break;
            case \M2E\OnBuy\Model\Product::ACTION_DELETE:
                $logAction = \M2E\OnBuy\Model\Listing\Log::ACTION_REMOVE_PRODUCT;
                break;
        }

        return $logAction;
    }
}
