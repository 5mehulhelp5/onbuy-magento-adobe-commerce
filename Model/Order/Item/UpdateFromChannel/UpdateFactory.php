<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order\Item\UpdateFromChannel;

class UpdateFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\OnBuy\Model\Order\Item $item,
        \M2E\OnBuy\Model\Channel\Order\Item $channelItem
    ): Update {
        return $this->objectManager->create(
            Update::class,
            [
                'item' => $item,
                'channelItem' => $channelItem,
            ]
        );
    }
}
