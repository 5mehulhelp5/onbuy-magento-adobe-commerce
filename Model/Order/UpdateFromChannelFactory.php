<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order;

class UpdateFromChannelFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\OnBuy\Model\Account $account,
        \M2E\OnBuy\Model\Site $site,
        bool $isNeedValidateByCreateDate
    ): UpdateFromChannel {
        return $this->objectManager->create(
            UpdateFromChannel::class,
            [
                'account' => $account,
                'site' => $site,
                'isNeedValidateByCreateDate' => $isNeedValidateByCreateDate,
            ]
        );
    }
}
