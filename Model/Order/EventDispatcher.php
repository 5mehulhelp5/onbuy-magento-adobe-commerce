<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order;

class EventDispatcher
{
    private const CHANEL_NAME = 'onbuy';
    private const REGION_EUROPE = 'europe';

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
            'channel_external_order_id' => $order->getChannelOrderId(),
            'magento_order_id' => (int)$order->getMagentoOrderId(),
            'magento_order_increment_id' => $order->getMagentoOrder()->getIncrementId(),
            'channel_purchase_date' => \DateTime::createFromImmutable($order->getPurchaseDate()),
            'region' => self::REGION_EUROPE,
        ]);
    }

    public function dispatchEventInvoiceCreated(\M2E\OnBuy\Model\Order $order): void
    {
        $this->eventManager->dispatch('ess_order_invoice_created', [
            'channel' => self::CHANEL_NAME,
            'channel_order_id' => (int)$order->getId(),
        ]);
    }
}
