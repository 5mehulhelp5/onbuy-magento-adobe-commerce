<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Connector\Order\Get;

use M2E\OnBuy\Model\Channel\Connector\Order\Get\Items\Response;

class ItemsCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private int $siteId;
    private \DateTimeInterface $updateFrom;
    private \DateTimeInterface $updateTo;
    private string $accountHash;

    public function __construct(
        string $accountHash,
        int $siteId,
        \DateTimeInterface $updateFrom,
        \DateTimeInterface $updateTo
    ) {
        $this->accountHash = $accountHash;
        $this->siteId = $siteId;
        $this->updateFrom = $updateFrom;
        $this->updateTo = $updateTo;
    }

    public function getCommand(): array
    {
        return ['order', 'get', 'items'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'site_id' => $this->siteId,
            'from_update_date' => $this->updateFrom->format('Y-m-d H:i:s'),
            'to_update_date' => $this->updateTo->format('Y-m-d H:i:s'),
        ];
    }

    public function parseResponse(
        \M2E\Core\Model\Connector\Response $response
    ): Items\Response {
        $responseData = $response->getResponseData();

        if (!array_key_exists('orders', $responseData)) {
            throw new \M2E\OnBuy\Model\Exception('Server don`t return "orders" array');
        }

        if (!array_key_exists('to_update_date', $responseData)) {
            throw new \M2E\OnBuy\Model\Exception('Server don`t return "to_update_date" date');
        }

        $orders = [];
        foreach ($responseData['orders'] as $order) {
            /** @var \M2E\OnBuy\Model\Channel\Order\Item[] $orderItem */
            $orderItem = [];
            foreach ($order['items'] as $item) {
                $tracking = null;
                if (isset($item['tracking'])) {
                    $tracking = new \M2E\OnBuy\Model\Channel\Order\Item\Tracking(
                        $item['tracking']['supplier_name'],
                        $item['tracking']['tracking_number'],
                        $item['tracking']['tracking_url'],
                    );
                }

                $orderItem[] = new \M2E\OnBuy\Model\Channel\Order\Item(
                    $item['name'],
                    $item['sku'],
                    $item['channel_product_id'],
                    (int)$item['qty'],
                    (int)$item['qty_dispatched'],
                    new \M2E\OnBuy\Model\Channel\Order\Item\Price(
                        (float)$item['price']['delivery_total'],
                        (float)$item['price']['unit'],
                        (float)$item['price']['total'],
                    ),
                    \M2E\Core\Helper\Date::createImmutableDateGmt($item['expected_dispatch_date']),
                    $item['fee'],
                    new \M2E\OnBuy\Model\Channel\Order\Item\Tax(
                        (float)$item['tax']['tax_delivery'],
                        (float)$item['tax']['tax_product'],
                        (float)$item['tax']['tax_total'],
                        (float)$item['tax']['tax_scheme'],
                    ),
                    $tracking
                );
            }

            $orders[] = new \M2E\OnBuy\Model\Channel\Order(
                $order['id'],
                $order['status'],
                new \M2E\OnBuy\Model\Channel\Order\Price(
                    (float)$order['price']['total'],
                    (float)$order['price']['subtotal'],
                    (float)$order['price']['delivery'],
                    (float)$order['price']['discount'],
                ),
                new \M2E\OnBuy\Model\Channel\Order\SalesFee(
                    (float)$order['sales_fee']['ex'],
                    (float)$order['sales_fee']['inc'],
                ),
                $order['currency_code'],
                new \M2E\OnBuy\Model\Channel\Order\Tax(
                    (float)$order['tax']['tax_total'],
                    (float)$order['tax']['tax_subtotal'],
                    (float)$order['tax']['tax_delivery'],
                ),
                new \M2E\OnBuy\Model\Channel\Order\Buyer(
                    $order['buyer']['name'],
                    $order['buyer']['email'],
                    $order['buyer']['phone'],
                ),
                new \M2E\OnBuy\Model\Channel\Order\BillingAddress(
                    $order['billing_address']['name'],
                    $order['billing_address']['line_1'],
                    $order['billing_address']['line_2'],
                    $order['billing_address']['line_3'],
                    $order['billing_address']['city'],
                    $order['billing_address']['county'],
                    $order['billing_address']['postcode'],
                    $order['billing_address']['country'],
                    $order['billing_address']['country_code'],
                ),
                new \M2E\OnBuy\Model\Channel\Order\DeliveryAddress(
                    $order['delivery_address']['name'],
                    $order['delivery_address']['line_1'],
                    $order['delivery_address']['line_2'],
                    $order['delivery_address']['line_3'],
                    $order['delivery_address']['city'],
                    $order['delivery_address']['county'],
                    $order['delivery_address']['postcode'],
                    $order['delivery_address']['country'],
                    $order['delivery_address']['country_code'],
                ),
                $order['stripe_transaction_id'],
                $order['paypal_capture_id'],
                $order['delivery_service'],
                \M2E\Core\Helper\Date::tryCreateImmutableDateGmt($order['shipped_date']),
                \M2E\Core\Helper\Date::tryCreateImmutableDateGmt($order['cancelled_date']),
                \M2E\Core\Helper\Date::createImmutableDateGmt($order['create_date']),
                \M2E\Core\Helper\Date::createImmutableDateGmt($order['update_date']),
                $order['fee'],
                $orderItem
            );
        }

        return new Response(
            $orders,
            \M2E\Core\Helper\Date::createImmutableDateGmt($responseData['to_update_date']),
            $response->getMessageCollection()
        );
    }
}
