<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Order;

class AssignProduct extends \M2E\OnBuy\Controller\Adminhtml\AbstractOrder
{
    private \M2E\OnBuy\Model\Order\Item\Repository $orderItemRepository;
    private \M2E\OnBuy\Model\Order\Item\ProductAssignService $productAssignService;
    private \M2E\OnBuy\Model\Magento\ProductFactory $magentoProductFactory;

    public function __construct(
        \M2E\OnBuy\Model\Magento\ProductFactory $magentoProductFactory,
        \M2E\OnBuy\Model\Order\Item\ProductAssignService $productAssignService,
        \M2E\OnBuy\Model\Order\Item\Repository $orderItemRepository,
        \M2E\OnBuy\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);
        $this->magentoProductFactory = $magentoProductFactory;
        $this->productAssignService = $productAssignService;
        $this->orderItemRepository = $orderItemRepository;
    }

    public function execute()
    {
        $productId = $this->getRequest()->getParam('product_id', false);
        $orderItemId = (int)$this->getRequest()->getParam('order_item_id');

        $orderItem = $this->orderItemRepository->find($orderItemId);

        if (
            empty($productId)
            || $orderItem === null
        ) {
            $this->setJsonContent(['error' => (string)__('Please specify Required Options.')]);

            return $this->getResult();
        }

        $magentoProduct = $this->magentoProductFactory->createByProductId((int)$productId);
        $magentoProduct->setStoreId($orderItem->getStoreId());

        if (!$magentoProduct->exists()) {
            $this->setJsonContent(['error' => (string)__('Product does not exist.')]);

            return $this->getResult();
        }

        $this->productAssignService->assign(
            $orderItem,
            $magentoProduct->getProduct(),
            \M2E\Core\Helper\Data::INITIATOR_USER
        );

        $this->setJsonContent([
            'success' => (string)__('Order Item was Linked.'),
            'continue' => $magentoProduct->isProductWithVariations(),
        ]);

        return $this->getResult();
    }
}
