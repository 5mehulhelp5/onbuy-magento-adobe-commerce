<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product;

use M2E\OnBuy\Model\Channel\Connector\Product\Search\SearchByIdentifierCommand;

class SearchChannelProductsService
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
     * @param array $identifiers
     *
     * @return \M2E\OnBuy\Model\Channel\Connector\Product\Search\Product[]
     * @throws \M2E\OnBuy\Model\Exception
     * @throws \M2E\Core\Model\Exception\Connection
     */
    public function findByIdentifiers(
        \M2E\OnBuy\Model\Account $account,
        \M2E\OnBuy\Model\Site $site,
        array $identifiers
    ): array {
        $result = [];
        foreach (array_chunk($identifiers, SearchByIdentifierCommand::MAX_IDENTIFIER_FOR_REQUEST) as $identifierPack) {
            $command = new SearchByIdentifierCommand(
                $account->getServerHash(),
                $site->getSiteId(),
                $identifierPack
            );

            /** @var \M2E\OnBuy\Model\Channel\Connector\Product\Search\Response $response */
            $response = $this->serverClient->process($command);
            if (empty($response->getProducts())) {
                continue;
            }

            array_push($result, ...$response->getProducts());
        }

        return $result;
    }
}
