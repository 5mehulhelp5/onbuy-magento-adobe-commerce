<?php

declare(strict_types=1);

namespace M2E\OnBuy\Observer;

class Creditmemo extends AbstractObserver
{
    private \M2E\OnBuy\Model\Order\Repository $repository;
    private \M2E\OnBuy\Model\Order\Cancel $cancel;

    public function __construct(
        \M2E\OnBuy\Model\Order\Repository $repository,
        \M2E\OnBuy\Model\Order\Cancel $cancel
    ) {
        $this->repository = $repository;
        $this->cancel = $cancel;
    }

    protected function process(): void
    {
        /** @var \Magento\Sales\Model\Order\Creditmemo $creditmemo */
        $creditmemo = $this->getEvent()->getCreditmemo();
        $magentoOrderId = (int)$creditmemo->getOrderId();

        $order = $this->repository->findByMagentoOrderId($magentoOrderId);
        if ($order === null) {
            return;
        }

        $this->cancel->process($order, \M2E\Core\Helper\Data::INITIATOR_USER);
    }
}
