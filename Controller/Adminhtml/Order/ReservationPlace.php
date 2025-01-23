<?php

namespace M2E\OnBuy\Controller\Adminhtml\Order;

use M2E\OnBuy\Controller\Adminhtml\AbstractOrder;

class ReservationPlace extends AbstractOrder
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

        $orders = $this->orderRepository->findOrdersForReservationPlace($ids);

        try {
            $actionSuccessful = false;

            foreach ($orders as $order) {
                $order->getLogService()->setInitiator(\M2E\Core\Helper\Data::INITIATOR_USER);

                if (!$order->isReservable()) {
                    continue;
                }

                if ($order->getReserve()->place()) {
                    $actionSuccessful = true;
                }
            }

            if ($actionSuccessful) {
                $this->messageManager->addSuccess(
                    __('QTY for selected Order(s) was reserved.')
                );
            } else {
                $this->messageManager->addError(
                    __('QTY for selected Order(s) was not reserved.')
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __(
                    'QTY for selected Order(s) was not reserved. Reason: %error_message',
                    ['error_message' => $e->getMessage()],
                )
            );
        }

        $this->_redirect($this->redirect->getRefererUrl());
    }
}
