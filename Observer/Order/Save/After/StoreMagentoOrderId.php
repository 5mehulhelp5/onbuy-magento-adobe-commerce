<?php

namespace M2E\OnBuy\Observer\Order\Save\After;

class StoreMagentoOrderId extends \M2E\OnBuy\Observer\AbstractObserver
{
    private \M2E\OnBuy\Helper\Data\GlobalData $globalDataHelper;

    public function __construct(\M2E\OnBuy\Helper\Data\GlobalData $globalDataHelper)
    {
        $this->globalDataHelper = $globalDataHelper;
    }

    protected function process(): void
    {
        /** @var \Magento\Sales\Model\Order $magentoOrder */
        $magentoOrder = $this->getEvent()->getOrder();

        /** @var \M2E\OnBuy\Model\Order $order */
        $order = $this
            ->globalDataHelper
            ->getValue(\M2E\OnBuy\Model\Order::ADDITIONAL_DATA_KEY_IN_ORDER);
        $this->globalDataHelper
             ->unsetValue(\M2E\OnBuy\Model\Order::ADDITIONAL_DATA_KEY_IN_ORDER);

        if (empty($order)) {
            return;
        }

        if ($order->getMagentoOrderId() == $magentoOrder->getId()) {
            return;
        }

        $order->addData([
            'magento_order_id' => $magentoOrder->getId(),
            'magento_order_creation_failure' => \M2E\OnBuy\Model\Order::MAGENTO_ORDER_CREATION_FAILED_NO,
            'magento_order_creation_latest_attempt_date' => \M2E\Core\Helper\Date::createCurrentGmt()
                                                                                       ->format('Y-m-d H:i:s'),
        ]);

        $order->setMagentoOrder($magentoOrder);
        $order->save();
    }
}
