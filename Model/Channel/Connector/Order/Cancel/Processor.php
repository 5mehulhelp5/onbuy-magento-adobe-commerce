<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Connector\Order\Cancel;

class Processor
{
    private \M2E\OnBuy\Model\Connector\Client\Single $singleClient;

    public function __construct(\M2E\OnBuy\Model\Connector\Client\Single $singleClient)
    {
        $this->singleClient = $singleClient;
    }

    /**
     * @param \M2E\OnBuy\Model\Order $order
     *
     * @return \M2E\Core\Model\Connector\Response\Message[] not success messages
     * @throws \M2E\OnBuy\Model\Exception
     * @throws \M2E\Core\Model\Exception\Connection
     * @throws \M2E\OnBuy\Model\Order\Exception\UnableCancel
     */
    public function process(\M2E\OnBuy\Model\Order $order): array
    {
        $requestOrder = new \M2E\OnBuy\Model\Channel\Connector\Order\Cancel\Order($order->getChannelOrderId());

        $command = new \M2E\OnBuy\Model\Channel\Connector\Order\Cancel\EntityCommand(
            $order->getAccount()->getServerHash(),
            $order->getSite()->getSiteId(),
            $requestOrder,
        );

        /** @var \M2E\Core\Model\Connector\Response $response */
        $response = $this->singleClient->process($command);

        return array_merge(
            $response->getMessageCollection()->getErrors(),
            $response->getMessageCollection()->getWarnings(),
        );
    }
}
