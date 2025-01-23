<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Connector\Order\Cancel;

class EntityCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $accountHash;
    private int $siteId;
    private \M2E\OnBuy\Model\Channel\Connector\Order\Cancel\Order $order;

    public function __construct(
        string $accountHash,
        int $siteId,
        Order $order
    ) {
        $this->accountHash = $accountHash;
        $this->siteId = $siteId;
        $this->order = $order;
    }

    public function getCommand(): array
    {
        return ['order', 'cancel', 'entity'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'site_id' => $this->siteId,
            'order' => [
                'id' => $this->order->getOrderId(),
            ],
        ];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): object
    {
        return $response;
    }
}
