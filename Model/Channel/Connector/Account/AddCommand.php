<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Connector\Account;

class AddCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $consumerKey;
    private string $secretKey;
    private string $title;
    private int $sellerId;

    public function __construct(string $title, int $sellerId, string $consumerKey, string $secretKey)
    {
        $this->title = $title;
        $this->sellerId = $sellerId;
        $this->consumerKey = $consumerKey;
        $this->secretKey = $secretKey;
    }

    public function getCommand(): array
    {
        return ['account', 'add', 'entity'];
    }

    public function getRequestData(): array
    {
        return [
            'title' => $this->title,
            'seller_id' => $this->sellerId,
            'consumer_key' => $this->consumerKey,
            'secret_key' => $this->secretKey,
        ];
    }

    public function parseResponse(
        \M2E\Core\Model\Connector\Response $response
    ): \M2E\OnBuy\Model\Channel\Connector\Account\Add\Response {
        if ($response->getMessageCollection()->hasErrors()) {
            throw new \M2E\OnBuy\Model\Exception\UnableAccountCreate($response->getMessageCollection());
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

        return new \M2E\OnBuy\Model\Channel\Connector\Account\Add\Response(
            $responseData['hash'],
            new \M2E\OnBuy\Model\Channel\Account(
                $responseData['account']['identifier'],
                $responseData['account']['is_test'],
                $sitesCollection
            )
        );
    }
}
