<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product;

use M2E\OnBuy\Model\Product;

class UpdateFromChannel
{
    private \M2E\OnBuy\Model\Product\Repository $repository;
    private \M2E\OnBuy\Model\InstructionService $instructionService;
    private \M2E\OnBuy\Model\Listing\LogService $logService;
    private int $logActionId;
    /** @var \M2E\OnBuy\Model\Product\UpdateFromChannel\ProcessorFactory */
    private UpdateFromChannel\ProcessorFactory $changesProcessorFactory;
    private LockManager $lockManager;

    public function __construct(
        Repository $repository,
        \M2E\OnBuy\Model\InstructionService $instructionService,
        \M2E\OnBuy\Model\Listing\LogService $logService,
        \M2E\OnBuy\Model\Product\UpdateFromChannel\ProcessorFactory $changesProcessorFactory,
        \M2E\OnBuy\Model\Product\LockManager $lockManager
    ) {
        $this->repository = $repository;
        $this->instructionService = $instructionService;
        $this->logService = $logService;
        $this->changesProcessorFactory = $changesProcessorFactory;
        $this->lockManager = $lockManager;
    }

    public function process(
        \M2E\OnBuy\Model\Channel\Product\ProductCollection $channelProductCollection,
        \M2E\OnBuy\Model\Account $account,
        \M2E\OnBuy\Model\Site $site
    ): void {
        if ($channelProductCollection->empty()) {
            return;
        }

        $existed = $this->repository->findByAnySkus(
            $channelProductCollection->getProductsSku(),
            $account->getId(),
            $site->getId()
        );

        foreach ($existed as $product) {
            $existedProductSku = empty($product->getOnlineSku()) ? $product->getMagentoSku() : $product->getOnlineSku();
            if (empty($existedProductSku)) {
                continue;
            }

            if (!$channelProductCollection->has($existedProductSku)) { // fix for duplicate products
                continue;
            }

            if (
                $product->isStatusNotListed() // list channel issue
                && $this->lockManager->isLocked($product)
            ) {
                continue;
            }

            $channelProduct = $channelProductCollection->get($existedProductSku);

            $changesProcessor = $this->changesProcessorFactory->create($product, $channelProduct);

            $changeResult = $changesProcessor->processChanges();

            if ($changeResult->isChangedProduct()) {
                $this->repository->save($product);
            }

            $this->writeInstructions($changeResult->getInstructionsData());
            $this->writeLogs($product, $changeResult->getLogs());
        }
    }

    private function writeInstructions(array $instructionsData): void
    {
        if (empty($instructionsData)) {
            return;
        }

        $this->instructionService->createBatch($instructionsData);
    }

    /**
     * @param \M2E\OnBuy\Model\Product $product
     * @param \M2E\OnBuy\Model\Listing\Log\Record[] $records
     *
     * @return void
     */
    private function writeLogs(Product $product, array $records): void
    {
        if (empty($records)) {
            return;
        }

        foreach ($records as $record) {
            $this->logService->addRecordToProduct(
                $record,
                $product,
                \M2E\Core\Helper\Data::INITIATOR_EXTENSION,
                \M2E\OnBuy\Model\Listing\Log::ACTION_CHANNEL_CHANGE,
                $this->getLogActionId(),
            );
        }
    }

    private function getLogActionId(): int
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        return $this->logActionId ?? ($this->logActionId = $this->logService->getNextActionId());
    }
}
