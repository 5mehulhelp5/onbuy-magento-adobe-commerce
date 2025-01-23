<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order\UpdateFromChannel;

class Create
{
    private \M2E\OnBuy\Model\Account $account;
    private \M2E\OnBuy\Model\Site $site;
    private \M2E\OnBuy\Model\Channel\Order $channelOrder;
    private \M2E\OnBuy\Model\Order\Repository $repository;
    private \M2E\OnBuy\Model\OrderFactory $orderFactory;
    private \M2E\OnBuy\Model\Order\Item\UpdateFromChannelFactory $itemUpdateFromChannelFactory;

    public function __construct(
        \M2E\OnBuy\Model\Account $account,
        \M2E\OnBuy\Model\Site $site,
        \M2E\OnBuy\Model\Channel\Order $channelOrder,
        \M2E\OnBuy\Model\Order\Repository $repository,
        \M2E\OnBuy\Model\OrderFactory $orderFactory,
        \M2E\OnBuy\Model\Order\Item\UpdateFromChannelFactory $itemUpdateFromChannelFactory
    ) {
        $this->account = $account;
        $this->site = $site;
        $this->channelOrder = $channelOrder;
        $this->repository = $repository;
        $this->orderFactory = $orderFactory;
        $this->itemUpdateFromChannelFactory = $itemUpdateFromChannelFactory;
    }

    public function process(): \M2E\OnBuy\Model\Order
    {
        $order = $this->handleCreate();
        $this->handleItems($order);

        $this->afterCreate($order);

        return $order;
    }

    private function handleCreate(): \M2E\OnBuy\Model\Order
    {
        $order = $this->orderFactory->createFromChannel($this->channelOrder, $this->account, $this->site);

        $this->repository->save($order);

        return $order;
    }

    private function handleItems(\M2E\OnBuy\Model\Order $order): void
    {
        foreach ($this->channelOrder->getOrderItems() as $orderItem) {
            $itemUpdateFromChannel = $this->itemUpdateFromChannelFactory->create(
                $order,
                $orderItem
            );

            $itemUpdateFromChannel->process();
        }

        $order->resetItems();
    }

    private function afterCreate(\M2E\OnBuy\Model\Order $order): void
    {
        $orderSettings = $this->account->getOrdersSettings();

        if (
            $order->hasListingProductItems()
            && !$orderSettings->isListingEnabled()
        ) {
            return;
        }

        if (
            $order->hasOtherListingItems()
            && !$orderSettings->isUnmanagedListingEnabled()
        ) {
            return;
        }

        if (!$order->canCreateMagentoOrder()) {
            $order->addWarningLog(
                'Magento Order was not created. Reason: %msg%',
                [
                    'msg' => 'Order Creation Rules were not met.',
                ]
            );
        }
    }
}
