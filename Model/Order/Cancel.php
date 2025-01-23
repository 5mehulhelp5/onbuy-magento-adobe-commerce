<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order;

class Cancel
{
    /** @var \M2E\OnBuy\Model\Order\ChangeCreateService */
    private ChangeCreateService $changeCreateService;

    public function __construct(\M2E\OnBuy\Model\Order\ChangeCreateService $changeCreateService)
    {
        $this->changeCreateService = $changeCreateService;
    }

    public function process(\M2E\OnBuy\Model\Order $order, int $initiator): void
    {
        if (!$order->canCancel()) {
            return;
        }

        if (!$order->getAccount()->getOrdersSettings()->isOrderCancelOrRefundOnChannelEnabled()) {
            return;
        }

        \M2E\Core\Helper\Data::validateInitiator($initiator);

        $this->changeCreateService->create(
            (int)$order->getId(),
            \M2E\OnBuy\Model\Order\Change::ACTION_CANCEL,
            $initiator,
            [],
        );
    }
}
