<?php

namespace M2E\OnBuy\Controller\Adminhtml\Order;

class AssignToMagentoProduct extends \M2E\OnBuy\Controller\Adminhtml\AbstractOrder
{
    public const MAPPING_PRODUCT = 'product';
    public const MAPPING_OPTIONS = 'options';

    private \M2E\OnBuy\Model\Order\Item\Repository $orderItemRepository;

    public function __construct(
        \M2E\OnBuy\Model\Order\Item\Repository $orderItemRepository,
        \M2E\OnBuy\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->orderItemRepository = $orderItemRepository;
    }

    public function execute()
    {
        $orderItemId = $this->getRequest()->getParam('order_item_id');
        $orderItem = $this->orderItemRepository->find($orderItemId);

        if ($orderItem === null) {
            $this->setJsonContent([
                'error' => __('Order Items does not exist.'),
            ]);

            return $this->getResult();
        }

        if (
            $orderItem->getMagentoProductId() === null
            || !$orderItem->getMagentoProduct()->exists()
        ) {
            $block = $this
                ->getLayout()
                ->createBlock(\M2E\OnBuy\Block\Adminhtml\Order\Item\Product\Mapping::class);

            $this->setJsonContent([
                'title' => __('Linking Product "%title"', ['title' => $orderItem->getChannelProductTitle()]),
                'html' => $block->toHtml(),
                'type' => self::MAPPING_PRODUCT,
            ]);

            return $this->getResult();
        }

        $this->setJsonContent([
            'error' => __('Product does not have Required Options.'),
        ]);

        return $this->getResult();
    }

    protected function getCustomViewNick(): string
    {
        return \M2E\OnBuy\Helper\View\OnBuy::NICK;
    }
}
