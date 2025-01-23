<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Order;

class RetrieveProcessor
{
    private \M2E\OnBuy\Model\Connector\Client\Single $singleClient;

    public function __construct(\M2E\OnBuy\Model\Connector\Client\Single $singleClient)
    {
        $this->singleClient = $singleClient;
    }

    public function process(
        \M2E\OnBuy\Model\Account $account,
        \M2E\OnBuy\Model\Site $site,
        \DateTimeInterface $updateFrom,
        \DateTimeInterface $updateTo
    ): \M2E\OnBuy\Model\Channel\Connector\Order\Get\Items\Response {
        $command = new \M2E\OnBuy\Model\Channel\Connector\Order\Get\ItemsCommand(
            $account->getServerHash(),
            $site->getSiteId(),
            $updateFrom,
            $updateTo,
        );

        /** @var \M2E\OnBuy\Model\Channel\Connector\Order\Get\Items\Response */
        return $this->singleClient->process($command);
    }
}
