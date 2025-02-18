<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order\MagentoProcessor;

class HandleOrder
{
    private \M2E\OnBuy\Model\Magento\Order\Updater $magentoOrderUpdater;
    private \M2E\OnBuy\Model\Order\Repository $orderRepository;

    public function __construct(
        \M2E\OnBuy\Model\Magento\Order\Updater $magentoOrderUpdater,
        \M2E\OnBuy\Model\Order\Repository $orderRepository
    ) {
        $this->magentoOrderUpdater = $magentoOrderUpdater;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param \M2E\OnBuy\Model\Order $order
     * @param int $initiator
     * @param bool $addLogAboutCreate
     *
     * @return void
     * @throws \M2E\OnBuy\Model\Order\Exception\UnableCreateMagentoOrder
     */
    public function process(
        \M2E\OnBuy\Model\Order $order,
        int $initiator,
        bool $addLogAboutCreate
    ): void {
        $this->createMagentoOrderIfNeed($order, $initiator, $addLogAboutCreate);
        $this->updateMagentoOrderIfNeed($order);
    }

    private function createMagentoOrderIfNeed(
        \M2E\OnBuy\Model\Order $order,
        int $initiator,
        bool $addLogAboutCreate
    ): void {
        if (!$order->canCreateMagentoOrder()) {
            $order->resetMagentoCreationAttempts();

            $this->orderRepository->save($order);

            return;
        }

        $order->getLogService()->setInitiator($initiator);

        if ($addLogAboutCreate) {
            $this->writeLogAboutCreate($order);
        }

        try {
            $order->createMagentoOrder();
        } catch (\Throwable $e) {
            throw new \M2E\OnBuy\Model\Order\Exception\UnableCreateMagentoOrder(
                $e->getMessage(),
                ['order_id' => $order->getId()],
                0,
                $e
            );
        }
    }

    private function updateMagentoOrderIfNeed(\M2E\OnBuy\Model\Order $order): void
    {
        if (
            !$order->getAccount()->getOrdersSettings()->isOrderStatusMappingModeDefault()
            || $order->getStatusUpdateRequired()
        ) {
            $magentoOrder = $order->getMagentoOrder();
            if ($magentoOrder === null) {
                return;
            }

            $this->magentoOrderUpdater->setMagentoOrder($magentoOrder);
            $this->magentoOrderUpdater->updateStatus($order->getStatusForMagentoOrder());

            $this->magentoOrderUpdater->finishUpdate();
        }
    }

    private function writeLogAboutCreate(\M2E\OnBuy\Model\Order $order): void
    {
        $order->addInfoLog(
            strtr(
                'Magento order creation rules are met. M2E channel_title will attempt to create Magento order.',
                [
                    'channel_title' => \M2E\OnBuy\Helper\Module::getChannelTitle(),
                ]
            ),
            [],
            [],
            true
        );
    }
}
