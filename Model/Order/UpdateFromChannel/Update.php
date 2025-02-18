<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order\UpdateFromChannel;

class Update
{
    private bool $statusChanged = false;
    private \M2E\OnBuy\Model\Order $order;
    private \M2E\OnBuy\Model\Channel\Order $channelOrder;
    private \M2E\OnBuy\Model\Order\Repository $repository;
    private \M2E\OnBuy\Model\Magento\Order\Updater $magentoOrderUpdater;
    private \M2E\OnBuy\Model\Order\Item\UpdateFromChannelFactory $itemUpdateFromChannelFactory;

    public function __construct(
        \M2E\OnBuy\Model\Order $order,
        \M2E\OnBuy\Model\Channel\Order $channelOrder,
        \M2E\OnBuy\Model\Order\Repository $repository,
        \M2E\OnBuy\Model\Magento\Order\Updater $magentoOrderUpdater,
        \M2E\OnBuy\Model\Order\Item\UpdateFromChannelFactory $itemUpdateFromChannelFactory
    ) {
        $this->order = $order;
        $this->channelOrder = $channelOrder;
        $this->repository = $repository;
        $this->magentoOrderUpdater = $magentoOrderUpdater;
        $this->itemUpdateFromChannelFactory = $itemUpdateFromChannelFactory;
    }

    public function process(): void
    {
        $this->handleUpdate();
        $this->handleItems();
        $this->afterUpdate();
    }

    private function handleUpdate(): void
    {
        if ($this->order->getPurchaseDate() > $this->channelOrder->getCreateDate()) {
            return;
        }

        $oldStatus = $this->order->getStatus();

        $wasChanged = \M2E\OnBuy\Model\OrderFactory::updateFromChannel($this->order, $this->channelOrder);
        if (!$wasChanged) {
            return;
        }

        $this->repository->save($this->order);

        if ($oldStatus !== $this->order->getStatus()) {
            $this->statusChanged = true;
        }
    }

    private function handleItems(): void
    {
        foreach ($this->channelOrder->getOrderItems() as $orderItem) {
            $itemUpdateFromChannel = $this->itemUpdateFromChannelFactory->create(
                $this->order,
                $orderItem
            );

            $itemUpdateFromChannel->process();
        }

        $this->order->resetItems();
    }

    private function afterUpdate(): void
    {
        $this->afterHandleOrder();
        $this->afterHandleMagento();
    }

    private function afterHandleOrder(): void
    {
        if ($this->statusChanged) {
            $this->order->addSuccessLog(
                sprintf(
                    'Order status was updated to %s on %s',
                    \M2E\OnBuy\Model\Order::getStatusTitle($this->order->getStatus()),
                    \M2E\OnBuy\Helper\Module::getChannelTitle()
                )
            );
        }
    }

    private function afterHandleMagento(): void
    {
        if (!$this->isUpdatedSomething()) {
            return;
        }

        $magentoOrder = $this->order->getMagentoOrder();
        if ($magentoOrder === null) {
            return;
        }

        $magentoOrderUpdater = $this->magentoOrderUpdater;
        $magentoOrderUpdater->setMagentoOrder($magentoOrder);
        $magentoOrderUpdater->updateStatus($this->order->getStatusForMagentoOrder());

        $proxy = $this->order->getProxy();
        $proxy->setStore($this->order->getStore());

        $magentoOrderUpdater->finishUpdate();
    }

    private function isUpdatedSomething(): bool
    {
        return $this->statusChanged;
    }
}
