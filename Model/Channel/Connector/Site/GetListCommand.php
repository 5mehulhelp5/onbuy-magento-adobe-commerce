<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Connector\Site;

class GetListCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $serverHash;

    public function __construct(string $serverHash)
    {
        $this->serverHash = $serverHash;
    }

    public function getCommand(): array
    {
        return ['site', 'get', 'entities'];
    }

    public function getRequestData(): array
    {
        return ['account' => $this->serverHash];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): \M2E\OnBuy\Model\Channel\SiteCollection
    {
        $collection = new \M2E\OnBuy\Model\Channel\SiteCollection();
        foreach ($response->getResponseData()['sites'] as $siteData) {
            $collection->add(
                new \M2E\OnBuy\Model\Channel\Site(
                    $siteData['id'],
                    $siteData['name'],
                    $siteData['country_code'],
                    $siteData['currency_code'],
                )
            );
        }

        return $collection;
    }
}
