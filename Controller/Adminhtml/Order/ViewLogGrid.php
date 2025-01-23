<?php

namespace M2E\OnBuy\Controller\Adminhtml\Order;

class ViewLogGrid extends \M2E\OnBuy\Controller\Adminhtml\AbstractOrder
{
    /** @var \M2E\OnBuy\Helper\Data\GlobalData */
    private $globalData;
    private \M2E\OnBuy\Model\Order\Repository $orderRepository;

    public function __construct(
        \M2E\OnBuy\Model\Order\Repository $orderRepository,
        \M2E\OnBuy\Helper\Data\GlobalData $globalData,
        \M2E\OnBuy\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->globalData = $globalData;
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $order = $this->orderRepository->get($id);

        $this->globalData->setValue('order', $order);
        $grid = $this->getLayout()->createBlock(\M2E\OnBuy\Block\Adminhtml\Order\View\Log\Grid::class);

        $this->setAjaxContent($grid->toHtml());

        return $this->getResult();
    }
}
