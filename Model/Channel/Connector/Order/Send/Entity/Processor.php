<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Connector\Order\Send\Entity;

class Processor
{
    private \M2E\OnBuy\Model\Connector\Client\Single $singleClient;

    public function __construct(\M2E\OnBuy\Model\Connector\Client\Single $singleClient)
    {
        $this->singleClient = $singleClient;
    }

    public function process(
        \M2E\OnBuy\Model\Account $account,
        \M2E\OnBuy\Model\Site $site,
        \M2E\OnBuy\Model\Channel\Connector\Order\Send\Entity\Order $order
    ): \M2E\OnBuy\Model\Channel\Connector\Order\Send\Entity\Response {
        $command = new \M2E\OnBuy\Model\Channel\Connector\Order\Send\Entity\Command(
            $account->getServerHash(),
            $site->getSiteId(),
            $order,
        );

        /** @var \M2E\OnBuy\Model\Channel\Connector\Order\Send\Entity\Response */
        return $this->singleClient->process($command);
    }
}
