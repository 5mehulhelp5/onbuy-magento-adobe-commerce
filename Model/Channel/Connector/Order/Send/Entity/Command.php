<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Connector\Order\Send\Entity;

class Command implements \M2E\Core\Model\Connector\CommandInterface
{
    private \M2E\OnBuy\Model\Channel\Connector\Order\Send\Entity\Order $order;
    private string $accountHash;
    private int $siteId;

    public function __construct(
        string $accountHash,
        int $siteId,
        \M2E\OnBuy\Model\Channel\Connector\Order\Send\Entity\Order $order
    ) {
        $this->order = $order;
        $this->accountHash = $accountHash;
        $this->siteId = $siteId;
    }

    public function getCommand(): array
    {
        return ['Order', 'Send', 'Entity'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'site_id' => $this->siteId,
            'order' => [
                'id' => $this->order->id,
                'tracking_info' => $this->order->tracking,
                'products' => $this->order->products,
            ]
        ];
    }

    public function parseResponse(
        \M2E\Core\Model\Connector\Response $response
    ): \M2E\OnBuy\Model\Channel\Connector\Order\Send\Entity\Response {
        $errorMessages = [];
        $warningMessages = [];

        foreach ($response->getMessageCollection()->getMessages() as $message) {
            if ($message->isError()) {
                $errorMessages[] = $message;
            }

            if ($message->isWarning()) {
                $warningMessages[] = $message;
            }
        }

        return new Response(
            empty($errorMessages),
            $errorMessages,
            $warningMessages
        );
    }
}
