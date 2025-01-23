<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Connector\Account;

class UpdateCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $consumerKey;
    private string $secretKey;
    private string $serverHash;

    public function __construct(string $serverHash, string $consumerKey, string $secretKey)
    {
        $this->serverHash = $serverHash;
        $this->consumerKey = $consumerKey;
        $this->secretKey = $secretKey;
    }

    public function getCommand(): array
    {
        return ['account', 'update', 'entity'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->serverHash,
            'consumer_key' => $this->consumerKey,
            'secret_key' => $this->secretKey,
        ];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): \M2E\OnBuy\Model\Channel\Account
    {
        if ($response->getMessageCollection()->hasErrors()) {
            throw new \M2E\OnBuy\Model\Exception\UnableAccountUpdate($response->getMessageCollection());
        }

        $responseData = $response->getResponseData();
        $sitesCollection = new \M2E\OnBuy\Model\Channel\SiteCollection();
        foreach ($responseData['sites'] as $siteData) {
            $sitesCollection->add(
                new \M2E\OnBuy\Model\Channel\Site(
                    $siteData['id'],
                    $siteData['name'],
                    $siteData['country_code'],
                    $siteData['currency_code'],
                )
            );
        }

        return new \M2E\OnBuy\Model\Channel\Account(
            $responseData['account']['identifier'],
            $responseData['account']['is_test'],
            $sitesCollection
        );
    }
}
