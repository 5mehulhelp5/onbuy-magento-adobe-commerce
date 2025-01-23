<?php

namespace M2E\OnBuy\Controller\Adminhtml\Order;

use M2E\OnBuy\Controller\Adminhtml\AbstractOrder;

class ReservationCancel extends AbstractOrder
{
    private \M2E\OnBuy\Model\Order\Repository $orderRepository;

    public function __construct(
        \M2E\OnBuy\Model\Order\Repository $orderRepository,
        $context = null
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        $ids = $this->getRequestIds();

        if (count($ids) == 0) {
            $this->messageManager->addError(__('Please select Order(s).'));
            $this->_redirect('*/*/index');

            return;
        }

        $orders = $this->orderRepository->findOrdersForReservationCancel($ids);

        try {
            $actionSuccessful = false;

            foreach ($orders as $order) {
                $order->getLogService()->setInitiator(\M2E\Core\Helper\Data::INITIATOR_USER);

                if ($order->getReserve()->cancel()) {
                    $actionSuccessful = true;
                    $order->getReserve()->addSuccessLogCancelQty();
                }
            }

            if ($actionSuccessful) {
                $this->messageManager->addSuccess(
                    __('QTY reserve for selected Order(s) was canceled.')
                );
            } else {
                $this->messageManager->addError(
                    __('QTY reserve for selected Order(s) was not canceled.')
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __(
                    'QTY reserve for selected Order(s) was not canceled. Reason: %error_message',
                    ['error_message' => $e->getMessage()],
                )
            );
        }

        $this->_redirect($this->redirect->getRefererUrl());
    }
}
