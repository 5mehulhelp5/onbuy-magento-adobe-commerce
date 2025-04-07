<?php

namespace M2E\OnBuy\Model\Channel\Attribute;

class Processor
{
    private \M2E\OnBuy\Model\Connector\Client\Single $serverClient;

    public function __construct(\M2E\OnBuy\Model\Connector\Client\Single $serverClient)
    {
        $this->serverClient = $serverClient;
    }

    public function process(
        string $serverHash,
        int $siteId,
        int $categoryId
    ): \M2E\OnBuy\Model\Channel\Connector\Attribute\Get\Response {
        $command = new \M2E\OnBuy\Model\Channel\Connector\Attribute\GetCommand(
            $serverHash,
            $siteId,
            $categoryId
        );

        /** @var \M2E\OnBuy\Model\Channel\Connector\Attribute\Get\Response */
        return $this->serverClient->process($command);
    }
}
