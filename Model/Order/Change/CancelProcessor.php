<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order\Change;

class CancelProcessor
{
    private const MAX_CHANGE_FOR_PROCESS = 50;

    private \M2E\OnBuy\Model\Order\Change\Repository $changeRepository;
    private \M2E\OnBuy\Model\Order\Repository $orderRepository;
    private \M2E\OnBuy\Model\Channel\Connector\Order\Cancel\Processor $connectorProcessor;

    public function __construct(
        \M2E\OnBuy\Model\Order\Change\Repository $changeRepository,
        \M2E\OnBuy\Model\Order\Repository $orderRepository,
        \M2E\OnBuy\Model\Channel\Connector\Order\Cancel\Processor $connectorProcessor
    ) {
        $this->changeRepository = $changeRepository;
        $this->orderRepository = $orderRepository;
        $this->connectorProcessor = $connectorProcessor;
    }

    public function process(\M2E\OnBuy\Model\Account $account): void
    {
        $changes = $this->changeRepository->findCanceledReadyForProcess(
            $account,
            self::MAX_CHANGE_FOR_PROCESS,
        );
        $processedOrders = [];
        foreach ($changes as $change) {
            if (!$account->getOrdersSettings()->isOrderCancelOrRefundOnChannelEnabled()) {
                $this->removeChange($change);

                continue;
            }

            if (isset($processedOrders[$change->getOrderId()])) {
                $this->removeChange($change);

                continue;
            }

            $processedOrders[$change->getOrderId()] = true;

            $order = $this->orderRepository->find($change->getOrderId());
            if ($order === null) {
                $this->removeChange($change);

                continue;
            }

            if (!$order->canCancel()) {
                $this->removeChange($change);

                continue;
            }

            $this->changeRepository->incrementAttemptCount([$change->getId()]);

            try {
                $notSuccessMessages = $this->connectorProcessor->process($order);
            } catch (\M2E\OnBuy\Model\Order\Exception\UnableCancel $e) {
                $this->removeChange($change);

                continue;
            }

            $this->removeChange($change);

            if (empty($notSuccessMessages)) {
                $order->addSuccessLog('Order is canceled. Status is updated on OnBuy.');

                continue;
            }

            foreach ($notSuccessMessages as $message) {
                if ($message->isError()) {
                    $order->addErrorLog(
                        'OnBuy order was not cancelled. Reason: %msg%',
                        ['msg' => $message->getText()],
                    );
                } else {
                    $order->addWarningLog($message->getText());
                }
            }
        }
    }

    private function removeChange(\M2E\OnBuy\Model\Order\Change $change): void
    {
        $this->changeRepository->delete($change);
    }
}
