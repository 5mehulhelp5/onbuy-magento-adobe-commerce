<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order\Change;

class ShippingProcessor
{
    private const MAX_CHANGE_FOR_PROCESS = 50;

    private \M2E\OnBuy\Model\Order\Change\ShippingProcessor\ChangeProcessor $changeProcessor;
    /** @var \M2E\OnBuy\Model\Order\Change\Repository */
    private Repository $changeRepository;
    private \M2E\OnBuy\Model\Order\Item\Repository $orderItemRepository;

    public function __construct(
        \M2E\OnBuy\Model\Order\Change\Repository $changeRepository,
        \M2E\OnBuy\Model\Order\Change\ShippingProcessor\ChangeProcessor $changeProcessor,
        \M2E\OnBuy\Model\Order\Item\Repository $orderItemRepository
    ) {
        $this->changeProcessor = $changeProcessor;
        $this->changeRepository = $changeRepository;
        $this->orderItemRepository = $orderItemRepository;
    }

    public function process(\M2E\OnBuy\Model\Account $account): void
    {
        $changes = $this->changeRepository->findShippingReadyForProcess($account, self::MAX_CHANGE_FOR_PROCESS);
        foreach ($changes as $change) {
            $change->incrementAttempts();

            $this->changeRepository->save($change);

            $result = $this->changeProcessor->process($account, $change);

            $this->changeRepository->delete($change);

            if ($result->isSkipped) {
                continue;
            }

            $order = $change->getOrder();

            if (!$result->isSuccess) {
                $this->processError($order, $result);

                continue;
            }

            $this->processSuccess($order, $result, $change);
        }
    }

    private function processError(
        \M2E\OnBuy\Model\Order $order,
        \M2E\OnBuy\Model\Order\Change\ShippingProcessor\ChangeResult $changeResult
    ): void {
        $errors = $changeResult->messages;

        $reason = array_shift($errors);
        $order->addErrorLog(
            'Channel Order was not updated with the tracking number "%tracking%" for "%carrier%". ' .
            'Reason: %reason%',
            [
                'reason' => $reason->getText(),
                '!tracking' => $changeResult->trackingNumber,
                '!carrier' => $changeResult->trackingTitle,
            ]
        );

        foreach ($errors as $errorMessage) {
            $order->addErrorLog($errorMessage->getText());
        }
    }

    private function processSuccess(
        \M2E\OnBuy\Model\Order $order,
        \M2E\OnBuy\Model\Order\Change\ShippingProcessor\ChangeResult $changeResult,
        \M2E\OnBuy\Model\Order\Change $change
    ): void {
        $order->addSuccessLog(
            'Tracking number "%num%" for "%code%" has been sent to %channel_title%.',
            [
                '!num' => $changeResult->trackingNumber,
                '!code' => $changeResult->trackingTitle,
                '!channel_title' => \M2E\OnBuy\Helper\Module::getChannelTitle(),
            ]
        );

        foreach ($changeResult->orderItems as $item) {
            $existQty = $item->getQtyDispatched();
            $newQty = $existQty + ($this->getItemsShippedQty($item, $change));
            $item->setQtyDispatched($newQty);
            $this->orderItemRepository->save($item);
        }

        foreach ($changeResult->messages as $message) {
            $order->addWarningLog($message->getText());
        }
    }

    private function getItemsShippedQty(
        \M2E\OnBuy\Model\Order\Item $item,
        \M2E\OnBuy\Model\Order\Change $change
    ): ?int {
        $shippedQty = null;
        foreach ($change->getOrderItemsForShipping() as $shipmentItem) {
            if ((int)$shipmentItem['item_id'] === (int)$item->getId()) {
                $shippedQty = (int)$shipmentItem['qty'];
                break;
            }
        }

        return $shippedQty;
    }
}
