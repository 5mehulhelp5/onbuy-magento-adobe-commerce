<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order\MagentoProcessor\Check;

class ParallelProcess
{
    private \M2E\OnBuy\Model\Order\Repository $orderRepository;

    public function __construct(\M2E\OnBuy\Model\Order\Repository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * This is going to protect from Magento Orders duplicates.
     * (Is assuming that there may be a parallel process that has already created Magento Order)
     * But this protection is not covering cases when two parallel cron processes are isolated by mysql transactions
     */
    public function isOrderChangedInParallelProcess(\M2E\OnBuy\Model\Order $order): bool
    {
        $dbOrder = $this->orderRepository->find((int)$order->getId());
        if ($dbOrder === null) {
            return false;
        }

        if ($dbOrder->getMagentoOrderId() !== $order->getMagentoOrderId()) {
            return true;
        }

        return false;
    }
}
