<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model;

class OrderFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    public function createEmpty(): Order
    {
        return $this->objectManager->create(Order::class);
    }

    public function createFromChannel(Channel\Order $channelOrder, Account $account, Site $site): Order
    {
        $obj = $this->createEmpty();
        $obj->create(
            $account,
            $site,
            $channelOrder->getOrderId(),
            $channelOrder->getCreateDate(),
            $channelOrder->getCurrency()
        );

        $obj->setStatus(self::resolveStatus($channelOrder->getStatus()))
            // ----------------------------------------
            ->setPriceSubtotal($channelOrder->getPrice()->subtotal)
            ->setPriceTotal($channelOrder->getPrice()->total)
            ->setPriceDelivery($channelOrder->getPrice()->delivery)
            ->setPriceDiscount($channelOrder->getPrice()->discount)
            ->setSalesFee(self::createSalesFee($channelOrder->getSalesFee()))
            // ----------------------------------------
            ->setBuyerName($channelOrder->getBuyer()->name)
            ->setBuyerEmail($channelOrder->getBuyer()->email)
            ->setBuyerPhone($channelOrder->getBuyer()->phone)
            // ----------------------------------------
            ->setBillingAddress(self::createBillingAddress($channelOrder->getBillingAddress()))
            ->setPaymentDetails(self::createPaymentDetails($channelOrder))
            ->setShippingDetails(self::createShippingDetails($channelOrder))
            ->setTaxDetails(self::createTaxDetails($channelOrder))
            // ----------------------------------------
            ->setShippedDate($channelOrder->getShippedDate())
            ->setCancelledDate($channelOrder->getCancelledDate())
            ->setChannelUpdateDate($channelOrder->getUpdateDate())
            // ----------------------------------------
            ->setFee($channelOrder->getFee())
        ;

        return $obj;
    }

    /**
     * @param \M2E\OnBuy\Model\Order $order
     * @param \M2E\OnBuy\Model\Channel\Order $channelOrder
     *
     * @return bool - was updated
     */
    public static function updateFromChannel(Order $order, Channel\Order $channelOrder): bool
    {
        $wasChanged = false;
        if ($order->getStatus() !== self::resolveStatus($channelOrder->getStatus())) {
            $order->setStatus(self::resolveStatus($channelOrder->getStatus()));

            $wasChanged = true;
        }

        if ($order->getPriceSubtotal() !== $channelOrder->getPrice()->subtotal) {
            $order->setPriceSubtotal($channelOrder->getPrice()->subtotal);

            $wasChanged = true;
        }

        if ($order->getPriceTotal() !== $channelOrder->getPrice()->total) {
            $order->setPriceTotal($channelOrder->getPrice()->total);

            $wasChanged = true;
        }

        if ($order->getPriceDelivery() !== $channelOrder->getPrice()->delivery) {
            $order->setPriceDelivery($channelOrder->getPrice()->delivery);

            $wasChanged = true;
        }

        if ($order->getPriceDiscount() !== $channelOrder->getPrice()->discount) {
            $order->setPriceDiscount($channelOrder->getPrice()->discount);

            $wasChanged = true;
        }

        if ($order->getSalesFee() !== self::createSalesFee($channelOrder->getSalesFee())) {
            $order->setSalesFee(self::createSalesFee($channelOrder->getSalesFee()));

            $wasChanged = true;
        }

        if ($order->getBuyerName() !== $channelOrder->getBuyer()->name) {
            $order->setBuyerName($channelOrder->getBuyer()->name);

            $wasChanged = true;
        }

        if ($order->getBuyerEmail() !== $channelOrder->getBuyer()->email) {
            $order->setBuyerEmail($channelOrder->getBuyer()->email);

            $wasChanged = true;
        }

        if ($order->getBuyerPhone() !== $channelOrder->getBuyer()->phone) {
            $order->setBuyerPhone($channelOrder->getBuyer()->phone);

            $wasChanged = true;
        }

        if ($order->getBillingAddress() !== self::createBillingAddress($channelOrder->getBillingAddress())) {
            $order->setBillingAddress(self::createBillingAddress($channelOrder->getBillingAddress()));

            $wasChanged = true;
        }

        if ($order->getPaymentDetails() !== self::createPaymentDetails($channelOrder)) {
            $order->setPaymentDetails(self::createPaymentDetails($channelOrder));

            $wasChanged = true;
        }

        if ($order->getShippingDetails() !== self::createShippingDetails($channelOrder)) {
            $order->setShippingDetails(self::createShippingDetails($channelOrder));

            $wasChanged = true;
        }

        if ($order->getTaxDetails() !== self::createTaxDetails($channelOrder)) {
            $order->setTaxDetails(self::createTaxDetails($channelOrder));

            $wasChanged = true;
        }

        if ($order->getShippedDate() != $channelOrder->getShippedDate()) {
            $order->setShippedDate($channelOrder->getShippedDate());

            $wasChanged = true;
        }

        if ($order->getCancelledDate() != $channelOrder->getCancelledDate()) {
            $order->setCancelledDate($channelOrder->getCancelledDate());

            $wasChanged = true;
        }

        if ($order->getChannelUpdateDate() != $channelOrder->getUpdateDate()) {
            $order->setChannelUpdateDate($channelOrder->getUpdateDate());

            $wasChanged = true;
        }

        if ($order->getFee() !== $channelOrder->getFee()) {
            $order->setFee($channelOrder->getFee());

            $wasChanged = true;
        }

        return $wasChanged;
    }

    // ----------------------------------------

    private static function resolveStatus(string $channelStatus): int
    {
        return \M2E\OnBuy\Model\Order\StatusResolver::resolve($channelStatus);
    }

    private static function createSalesFee(\M2E\OnBuy\Model\Channel\Order\SalesFee $salesFee): array
    {
        return [
            'ex' => $salesFee->ex,
            'inc' => $salesFee->inc,
        ];
    }

    private static function createBillingAddress(\M2E\OnBuy\Model\Channel\Order\BillingAddress $billingAddress): array
    {
        return [
            'name' => $billingAddress->name,
            'street' => [
                $billingAddress->line1,
                $billingAddress->line2,
                $billingAddress->line3,
            ],
            'city' => $billingAddress->city,
            'county' => $billingAddress->county,
            'postcode' => $billingAddress->postCode,
            'country' => $billingAddress->country,
            'country_code' => $billingAddress->countryCode,
        ];
    }

    private static function createPaymentDetails(Channel\Order $channelOrder): array
    {
        return [
            'stripe_transaction_id' => $channelOrder->getStripeTransactionId(),
            'paypal_capture_id' => $channelOrder->getPaypalCaptureId(),
        ];
    }

    private static function createShippingDetails(Channel\Order $channelOrder): array
    {
        $deliveryAddress = $channelOrder->getDeliveryAddress();

        return [
            'address' => [
                'recipient_name' => $deliveryAddress->name,
                'street' => [
                    $deliveryAddress->line1,
                    $deliveryAddress->line2,
                    $deliveryAddress->line3,
                ],
                'city' => $deliveryAddress->city,
                'state' => $deliveryAddress->country,
                'postal_code' => $deliveryAddress->postCode,
                'country' => $deliveryAddress->country,
                'country_code' => $deliveryAddress->countryCode,
            ],
            'service' => $channelOrder->getDeliveryService(),
        ];
    }

    private static function createTaxDetails(Channel\Order $channelOrder): array
    {
        $taxDetails = $channelOrder->getTaxDetails();

        $rate = null;
        if ($taxDetails->taxTotal !== null) {
            $rate = self::resolveTax(
                $channelOrder->getPrice()->total,
                $taxDetails->taxTotal
            );
        }

        return [
            'rate' => $rate,
            'amount' => $taxDetails->taxTotal,
            'tax_subtotal' => $taxDetails->taxSubtotal,
            'tax_delivery' => $taxDetails->taxDelivery,
        ];
    }

    /**
     * @return int|float
     */
    private static function resolveTax(float $totalPrice, float $taxTotal)
    {
        return \M2E\OnBuy\Model\Order\TaxResolver::calculateRate($totalPrice, $taxTotal);
    }
}
