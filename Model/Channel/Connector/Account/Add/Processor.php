<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Connector\Account\Add;

class Processor
{
    private \M2E\OnBuy\Model\Connector\Client\Single $serverClient;

    public function __construct(\M2E\OnBuy\Model\Connector\Client\Single $serverClient)
    {
        $this->serverClient = $serverClient;
    }

    /**
     * @param string $title
     * @param int $sellerId
     * @param string $consumerKey
     * @param string $secretKey
     *
     * @return \M2E\OnBuy\Model\Channel\Connector\Account\Add\Response
     * @throws \M2E\Core\Model\Exception
     * @throws \M2E\Core\Model\Exception\Connection
     * @throws \M2E\OnBuy\Model\Exception\UnableAccountCreate
     */
    public function process(string $title, int $sellerId, string $consumerKey, string $secretKey): Response
    {
        $command = new \M2E\OnBuy\Model\Channel\Connector\Account\AddCommand(
            $title,
            $sellerId,
            $consumerKey,
            $secretKey
        );

        /** @var Response */
        return $this->serverClient->process($command);
    }
}
