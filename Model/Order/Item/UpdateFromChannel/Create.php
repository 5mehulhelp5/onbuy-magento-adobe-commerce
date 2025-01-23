<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order\Item\UpdateFromChannel;

class Create
{
    private \M2E\OnBuy\Model\Order $order;
    private \M2E\OnBuy\Model\Channel\Order\Item $channelItem;
    private \M2E\OnBuy\Model\Order\ItemFactory $itemFactory;
    private \M2E\OnBuy\Model\Order\Item\Repository $repository;

    public function __construct(
        \M2E\OnBuy\Model\Order $order,
        \M2E\OnBuy\Model\Channel\Order\Item $channelItem,
        \M2E\OnBuy\Model\Order\ItemFactory $itemFactory,
        \M2E\OnBuy\Model\Order\Item\Repository $repository
    ) {
        $this->order = $order;
        $this->channelItem = $channelItem;
        $this->itemFactory = $itemFactory;
        $this->repository = $repository;
    }

    public function process(): \M2E\OnBuy\Model\Order\Item
    {
        $item = $this->handleCreate();

        return $item;
    }

    private function handleCreate(): \M2E\OnBuy\Model\Order\Item
    {
        $item = $this->itemFactory->createFromChannel($this->order, $this->channelItem);
        $this->repository->create($item);

        return $item;
    }
}
