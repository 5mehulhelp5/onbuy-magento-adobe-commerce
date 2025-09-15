<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\UpdateFromChannel;

use M2E\OnBuy\Model\Product;

class Processor
{
    private \M2E\OnBuy\Model\Product $product;
    private \M2E\OnBuy\Model\Channel\Product $channelProduct;
    /** @var \M2E\OnBuy\Model\Product\CalculateStatusByChannel */
    private Product\CalculateStatusByChannel $calculateStatusByChannel;

    private array $instructionsData = [];
    /** @var \M2E\OnBuy\Model\Listing\Log\Record[] */
    private array $logs = [];

    public function __construct(
        \M2E\OnBuy\Model\Product $product,
        \M2E\OnBuy\Model\Channel\Product $channelProduct,
        \M2E\OnBuy\Model\Product\CalculateStatusByChannel $calculateStatusByChannel
    ) {
        $this->product = $product;
        $this->channelProduct = $channelProduct;
        $this->calculateStatusByChannel = $calculateStatusByChannel;
    }

    public function processChanges(): ChangeResult
    {
        $isChangedProduct = $this->processProduct();

        return new ChangeResult(
            $this->product,
            $isChangedProduct,
            array_values($this->instructionsData),
            array_values($this->logs),
        );
    }

    private function processProduct(): bool
    {
        $isChangedProduct = false;

        if ($this->processStatus()) {
            $isChangedProduct = true;
        }

        if ($this->processQty()) {
            $isChangedProduct = true;
        }

        if ($this->processPrice()) {
            $isChangedProduct = true;
        }

        return $isChangedProduct;
    }

    // ----------------------------------------

    # region status
    private function processStatus(): bool
    {
        if (!$this->isNeedChangeStatus($this->product->getStatus(), $this->channelProduct->getStatus())) {
            return false;
        }

        $this->addInstructionData(
            Product::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED,
            80,
        );

        $calculatedStatus = $this->calculateStatusByChannel->calculate(
            $this->product,
            $this->channelProduct,
        );
        if ($calculatedStatus === null) {
            throw new \M2E\OnBuy\Model\Exception\Logic(
                'Unable calculate status of channel product.',
                [
                    'product' => $this->product->getId(),
                    'extension_status' => $this->product->getStatus(),
                    'channel_status' => $this->channelProduct->getStatus(),
                ],
            );
        }

        $isOldStatusNotListed = $this->product->isStatusNotListed();

        $this->addLog($this->processNewStatus($calculatedStatus));

        if (
            $isOldStatusNotListed
            && !$this->product->isStatusNotListed()
        ) {
            $this->processListedOnChannelIssue();
        }

        return true;
    }

    private function isNeedChangeStatus(int $productStatus, int $channelStatus): bool
    {
        return $productStatus !== $channelStatus;
    }

    private function processNewStatus(
        \M2E\OnBuy\Model\Product\CalculateStatusByChannel\Result $calculatedStatus
    ): \M2E\OnBuy\Model\Listing\Log\Record {
        switch ($calculatedStatus->getStatus()) {
            case \M2E\OnBuy\Model\Product::STATUS_NOT_LISTED:
                $this->product->setStatusNotListed($calculatedStatus->getStatusChanger());
                break;

            default:
                $this->product->setStatus($calculatedStatus->getStatus(), $calculatedStatus->getStatusChanger());
        }

        return $calculatedStatus->getMessageAboutChange();
    }

    private function processListedOnChannelIssue(): void
    {
        $this->product
            ->setOpc($this->channelProduct->getOpc())
            ->setChannelProductId($this->channelProduct->getChannelProductId())
            ->setOnlineQty($this->channelProduct->getQty())
            ->setOnlinePrice($this->channelProduct->getPrice())
            ->setOnlineSku($this->channelProduct->getSku())
            ->setOnlineGroupSku($this->channelProduct->getGroupSku())
            ->setProductLinkOnChannel($this->channelProduct->getProductUrl())
            ->setOnlineDeliveryTemplateId($this->channelProduct->getDeliveryTemplateId());
    }

    # endregion

    // ----------------------------------------

    # region qty

    private function processQty(): bool
    {
        if (!$this->isNeedChangeQty()) {
            return false;
        }

        $this->addInstructionData(
            Product::INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED,
            80,
        );

        $message = (string)__(
            'Item QTY was changed from %from to %to.',
            [
                'from' => $this->product->getOnlineQty(),
                'to' => $this->channelProduct->getQty(),
            ],
        );

        $this->addLog(
            new \M2E\OnBuy\Model\Listing\Log\Record(
                $message,
                \M2E\OnBuy\Model\Log\AbstractModel::TYPE_SUCCESS,
            ),
        );

        $this->product->setOnlineQty($this->channelProduct->getQty());

        return true;
    }

    private function isNeedChangeQty(): bool
    {
        if (!$this->product->isStatusListed()) {
            return false;
        }

        if ($this->product->getOnlineQty() === $this->channelProduct->getQty()) {
            return false;
        }

        if ($this->isNeedSkipQtyChange($this->product->getOnlineQty(), $this->channelProduct->getQty())) {
            return false;
        }

        return true;
    }

    private function isNeedSkipQtyChange(int $currentQty, int $channelQty): bool
    {
        if ($channelQty > $currentQty) {
            return false;
        }

        return $currentQty < 5;
    }

    # endregion

    // ----------------------------------------

    # region price

    private function processPrice(): bool
    {
        if (!$this->isNeedChangePrice()) {
            return false;
        }

        $this->addInstructionData(
            Product::INSTRUCTION_TYPE_CHANNEL_PRICE_CHANGED,
            60,
        );

        $message = (string)__(
            'Item Price was changed from %from to %to.',
            [
                'from' => $this->product->getOnlinePrice(),
                'to' => $this->channelProduct->getPrice(),
            ]
        );

        $this->addLog(
            new \M2E\OnBuy\Model\Listing\Log\Record(
                $message,
                \M2E\OnBuy\Model\Log\AbstractModel::TYPE_SUCCESS,
            ),
        );

        $this->product->setOnlinePrice($this->channelProduct->getPrice());

        return true;
    }

    private function isNeedChangePrice(): bool
    {
        if (!$this->product->isStatusListed()) {
            return false;
        }

        return $this->product->getOnlinePrice() !== $this->channelProduct->getPrice();
    }

    # endregion

    // ----------------------------------------

    private function addInstructionData(string $type, int $priority): void
    {
        $this->instructionsData[$type] = [
            'listing_product_id' => $this->product->getId(),
            'type' => $type,
            'priority' => $priority,
            'initiator' => 'channel_changes_synchronization',
        ];
    }

    private function addLog(\M2E\OnBuy\Model\Listing\Log\Record $record): void
    {
        $this->logs[$record->getMessage()] = $record;
    }
}
