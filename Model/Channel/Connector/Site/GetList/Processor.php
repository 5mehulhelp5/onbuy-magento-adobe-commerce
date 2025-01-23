<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Connector\Site\GetList;

class Processor
{
    private \M2E\OnBuy\Model\Connector\Client\Single $client;

    public function __construct(\M2E\OnBuy\Model\Connector\Client\Single $client)
    {
        $this->client = $client;
    }

    public function get(\M2E\OnBuy\Model\Account $account): \M2E\OnBuy\Model\Channel\SiteCollection
    {
        $command = new \M2E\OnBuy\Model\Channel\Connector\Site\GetListCommand($account->getServerHash());

        /** @var \M2E\OnBuy\Model\Channel\SiteCollection $response */
        $response = $this->client->process($command);

        return $response;
    }
}
