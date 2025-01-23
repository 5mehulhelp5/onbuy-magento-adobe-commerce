<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order\Note;

trait MagentoOrderUpdateTrait
{
    private \M2E\OnBuy\Model\Magento\Order\Updater $magentoOrderUpdater;

    private function updateMagentoOrderComment(
        \M2E\OnBuy\Model\Order $order,
        string $comment
    ): void {
        $magentoOrderModel = $order->getMagentoOrder();
        if ($magentoOrderModel === null) {
            return;
        }

        $this->magentoOrderUpdater->setMagentoOrder($magentoOrderModel);
        $this->magentoOrderUpdater->updateComments($comment);
        $this->magentoOrderUpdater->finishUpdate();
    }
}
