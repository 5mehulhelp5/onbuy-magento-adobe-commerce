<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order;

class EventDispatcher
{
    private const CHANEL_NAME = 'onbuy';

    private \Magento\Framework\Event\ManagerInterface $eventManager;

    public function __construct(\Magento\Framework\Event\ManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    public function dispatchEventsMagentoOrderCreated(\M2E\OnBuy\Model\Order $order): void
    {
        $this->eventManager->dispatch('m2e_onbuy_order_place_success', ['order' => $order]);

        $this->eventManager->dispatch('ess_magento_order_created', [
            'channel' => self::CHANEL_NAME,
            'channel_order_id' => (int)$order->getId(),
            'magento_order_id' => (int)$order->getMagentoOrderId(),
            'magento_order_increment_id' => $order->getMagentoOrder()->getIncrementId(),
            'channel_purchase_date' => $this->getPurchaseDate($order),
            'region' => '',
        ]);
    }

    public function dispatchEventInvoiceCreated(\M2E\OnBuy\Model\Order $order): void
    {
        $this->eventManager->dispatch('ess_order_invoice_created', [
            'channel' => self::CHANEL_NAME,
            'channel_order_id' => (int)$order->getId(),
        ]);
    }

    private function getPurchaseDate(\M2E\OnBuy\Model\Order $order): \DateTime
    {
        return \DateTime::createFromImmutable($order->getPurchaseDate());
    }
}
