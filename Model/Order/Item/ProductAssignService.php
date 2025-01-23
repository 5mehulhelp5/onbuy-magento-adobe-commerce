<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order\Item;

class ProductAssignService
{
    private \M2E\OnBuy\Model\Order\Item\Repository $orderItemRepository;

    public function __construct(
        \M2E\OnBuy\Model\Order\Item\Repository $orderItemRepository
    ) {
        $this->orderItemRepository = $orderItemRepository;
    }

    /**
     * @param \M2E\OnBuy\Model\Order\Item $orderItem
     * @param \Magento\Catalog\Model\Product $magentoProduct
     * @param int $initiator
     *
     * @return void
     * @throws \M2E\OnBuy\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function assign(
        \M2E\OnBuy\Model\Order\Item $orderItem,
        \Magento\Catalog\Model\Product $magentoProduct,
        int $initiator
    ): void {
        $orderItem->setMagentoProductId((int)$magentoProduct->getId());
        $this->orderItemRepository->save($orderItem);

        if ($initiator === \M2E\Core\Helper\Data::INITIATOR_EXTENSION) {
            return;
        }

        $orderItem->getOrder()->getLogService()->setInitiator($initiator);
        $orderItem->getOrder()->addSuccessLog(
            'Order Item "%title%" was Linked.',
            [
                'title' => $orderItem->getChannelProductTitle(),
            ]
        );
    }

    /**
     * @param \M2E\OnBuy\Model\Order\Item $orderItem
     *
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function unAssign(\M2E\OnBuy\Model\Order\Item $orderItem): void
    {
        if ($orderItem->getOrder()->getReserve()->isPlaced()) {
            $orderItem->getOrder()->getReserve()->cancel();
        }

        $orderItem->removeMagentoProductId();
        $orderItem->removeAssociatedProducts();
        $orderItem->removeAssociatedOptions();

        $this->orderItemRepository->save($orderItem);

        $orderItem->getOrder()->getLogService()->setInitiator(\M2E\Core\Helper\Data::INITIATOR_USER);
        $orderItem->getOrder()->addSuccessLog(
            'Item "%title%" was Unlinked.',
            [
                'title' => $orderItem->getChannelProductTitle(),
            ]
        );
    }
}
