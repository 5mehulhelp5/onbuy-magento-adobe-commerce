<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order\UpdateFromChannel;

class CreateFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\OnBuy\Model\Account $account,
        \M2E\OnBuy\Model\Site $site,
        \M2E\OnBuy\Model\Channel\Order $channelOrder
    ): Create {
        return $this->objectManager->create(
            Create::class,
            [
                'account' => $account,
                'site' => $site,
                'channelOrder' => $channelOrder,
            ]
        );
    }
}
