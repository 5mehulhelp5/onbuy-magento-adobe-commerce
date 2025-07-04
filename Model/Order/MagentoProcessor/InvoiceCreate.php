<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order\MagentoProcessor;

class InvoiceCreate
{
    private \M2E\OnBuy\Model\Magento\Order\InvoiceFactory $magentoInvoiceFactory;
    private \M2E\OnBuy\Model\Order\EventDispatcher $orderEventDispatcher;
    private \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender;
    private \M2E\OnBuy\Helper\Module\Exception $helperModuleException;

    public function __construct(
        \M2E\OnBuy\Model\Magento\Order\InvoiceFactory $magentoInvoiceFactory,
        \M2E\OnBuy\Model\Order\EventDispatcher $orderEventDispatcher,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \M2E\OnBuy\Helper\Module\Exception $helperModuleException
    ) {
        $this->magentoInvoiceFactory = $magentoInvoiceFactory;
        $this->orderEventDispatcher = $orderEventDispatcher;
        $this->invoiceSender = $invoiceSender;
        $this->helperModuleException = $helperModuleException;
    }

    public function process(
        \M2E\OnBuy\Model\Order $order
    ): void {
        if (!$this->canCreateInvoice($order)) {
            return;
        }

        $invoiceItems = $this->findItemsToInvoice($order);
        if (empty($invoiceItems)) {
            return;
        }

        try {
            $invoiceBuilder = $this->magentoInvoiceFactory->create($order->getMagentoOrder(), $invoiceItems);
            $invoice = $invoiceBuilder->create();

            if ($order->getAccount()->getOrdersSettings()->isCustomerNewNotifyWhenInvoiceCreated()) {
                $this->invoiceSender->send($invoice);
            }
        } catch (\Throwable $throwable) {
            $this->helperModuleException->process($throwable);
            $order->addErrorLog(
                'Invoice was not created. Reason: %msg%',
                ['msg' => $throwable->getMessage()]
            );

            return;
        }

        $this->orderEventDispatcher->dispatchEventInvoiceCreated($order);

        $order->addSuccessLog(
            'Invoice #%invoice_id% was created.',
            ['!invoice_id' => $invoice->getIncrementId()]
        );
    }

    private function canCreateInvoice(\M2E\OnBuy\Model\Order $order): bool
    {
        if (!$order->hasMagentoOrder()) {
            return false;
        }

        if (!$order->getAccount()->getInvoiceAndShipmentSettings()->isCreateMagentoInvoice()) {
            return false;
        }

        $magentoOrder = $order->getMagentoOrder();
        if ($magentoOrder === null) {
            return false;
        }

        if (!$magentoOrder->canInvoice()) {
            return false;
        }

        return true;
    }

    /**
     * @param \M2E\OnBuy\Model\Order $order
     *
     * @return \Magento\Sales\Model\Order\Item[]
     */
    private function findItemsToInvoice(\M2E\OnBuy\Model\Order $order): array
    {
        /** @var \Magento\Sales\Model\Order $magentoOrder */
        $magentoOrder = $order->getMagentoOrder();

        $orderItemsByProductId = [];
        foreach ($order->getItems() as $orderItem) {
            $orderItemsByProductId[$orderItem->getMagentoProductId()][] = $orderItem;
        }

        $itemsToInvoice = [];
        foreach ($magentoOrder->getAllItems() as $magentoOrderItem) {
            if (empty($orderItemsByProductId[$magentoOrderItem->getProductId()])) {
                continue;
            }

            if (empty($magentoOrderItem->getQtyToInvoice())) {
                continue;
            }

            $itemsToInvoice[] = $magentoOrderItem;
        }

        return $itemsToInvoice;
    }
}
