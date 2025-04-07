<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Category;

class Processor
{
    private \M2E\OnBuy\Model\Connector\Client\Single $serverClient;

    public function __construct(\M2E\OnBuy\Model\Connector\Client\Single $serverClient)
    {
        $this->serverClient = $serverClient;
    }

    public function process(
        int $siteId
    ): \M2E\OnBuy\Model\Channel\Connector\Category\Get\Response {
        $command = new \M2E\OnBuy\Model\Channel\Connector\Category\GetCommand($siteId);

        /** @var \M2E\OnBuy\Model\Channel\Connector\Category\Get\Response */
        return $this->serverClient->process($command);
    }
}
