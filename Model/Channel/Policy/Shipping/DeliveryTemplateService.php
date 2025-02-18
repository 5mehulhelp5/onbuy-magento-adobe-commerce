<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Policy\Shipping;

class DeliveryTemplateService
{
    private \M2E\OnBuy\Model\Connector\Client\Single $serverClient;

    public function __construct(
        \M2E\OnBuy\Model\Connector\Client\Single $serverClient
    ) {
        $this->serverClient = $serverClient;
    }

    /**
     * @param \M2E\OnBuy\Model\Account $account
     * @param \M2E\OnBuy\Model\Site $site
     *
     * @return \M2E\OnBuy\Model\Channel\Policy\Shipping\DeliveryTemplate\Collection
     * @throws \M2E\OnBuy\Model\Exception
     */
    public function retrieve(
        \M2E\OnBuy\Model\Account $account,
        \M2E\OnBuy\Model\Site $site
    ): \M2E\OnBuy\Model\Channel\Policy\Shipping\DeliveryTemplate\Collection {
        $command = new \M2E\OnBuy\Model\Channel\Connector\Policy\Shipping\DeliveryTemplate\GetCommand(
            $account->getServerHash(),
            $site->getSiteId()
        );
        /** @var \M2E\OnBuy\Model\Channel\Policy\Shipping\DeliveryTemplate\Collection $channelDeliveryTemplates */
        $channelDeliveryTemplates = $this->serverClient->process($command);

        return $channelDeliveryTemplates;
    }
}
