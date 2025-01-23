<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Connector\Account;

class GetAuthInfoCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private array $accountsServerHashes;

    public function __construct(array $accountsServerHashes)
    {
        $this->accountsServerHashes = $accountsServerHashes;
    }

    public function getCommand(): array
    {
        return ['account', 'get', 'authInfo'];
    }

    public function getRequestData(): array
    {
        return ['accounts' => $this->accountsServerHashes];
    }

    public function parseResponse(
        \M2E\Core\Model\Connector\Response $response
    ): \M2E\OnBuy\Model\Channel\Account\AuthInfoCollection {
        $collection = new \M2E\OnBuy\Model\Channel\Account\AuthInfoCollection();
        foreach ($response->getResponseData()['accounts'] as $accountServerHash => $accountData) {
            $collection->add($accountServerHash, $accountData['is_valid']);
        }

        return $collection;
    }
}
