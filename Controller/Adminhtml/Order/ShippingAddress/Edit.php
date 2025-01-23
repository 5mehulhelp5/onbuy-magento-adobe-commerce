<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Order\ShippingAddress;

use M2E\OnBuy\Controller\Adminhtml\Order\AbstractOrder;

class Edit extends AbstractOrder
{
    private \M2E\OnBuy\Model\Order\Repository $orderRepository;

    public function __construct(
        \M2E\OnBuy\Model\Order\Repository $orderRepository
    ) {
        parent::__construct();

        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        $orderId = $this->getRequest()->getParam('id');
        $order = $this->orderRepository->get((int)$orderId);

        $form = $this
            ->getLayout()
            ->createBlock(\M2E\OnBuy\Block\Adminhtml\Order\Edit\ShippingAddress\Form::class, '', [
                'order' => $order,
            ]);

        $this->setAjaxContent($form->toHtml());

        return $this->getResult();
    }
}
