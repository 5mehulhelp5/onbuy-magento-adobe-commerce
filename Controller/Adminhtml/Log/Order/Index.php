<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Log\Order;

class Index extends \M2E\OnBuy\Controller\Adminhtml\Log\AbstractOrder
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
        $orderId = $this->getRequest()->getParam('id', false);

        if ($orderId) {
            $order = $this->orderRepository->find((int)$orderId);
            if ($order === null) {
                $this->getMessageManager()->addError(__('Order does not exist.'));

                return $this->_redirect('*/*/index');
            }

            $this->setPageTitle(
                (string)__('Order #%order_id Log', ['order_id' => $order->getChannelOrderId()])
            );
        } else {
            $this->setPageTitle((string)__('Orders Logs & Events'));
        }

        $this->addContent($this->getLayout()->createBlock(\M2E\OnBuy\Block\Adminhtml\Log\Order::class));

        return $this->getResult();
    }

    private function setPageTitle(string $pageTitle): void
    {
        $this->getResult()->getConfig()->getTitle()->prepend($pageTitle);
    }
}
