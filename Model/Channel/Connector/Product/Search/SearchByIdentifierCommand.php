<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Connector\Product\Search;

class SearchByIdentifierCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    public const MAX_IDENTIFIER_FOR_REQUEST = 50;

    private string $accountServerHash;
    private int $siteId;
    private array $identifiers;

    public function __construct(string $accountServerHash, int $siteId, array $identifiers)
    {
        $this->accountServerHash = $accountServerHash;
        $this->siteId = $siteId;
        $this->identifiers = $identifiers;
        if (\count($this->identifiers) > self::MAX_IDENTIFIER_FOR_REQUEST) {
            throw new \LogicException('Ean pack so big');
        }
    }

    public function getCommand(): array
    {
        return ['Product', 'Search', 'ByIdentifiers'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountServerHash,
            'site_id' => $this->siteId,
            'identifiers' => $this->identifiers
        ];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): Response
    {
        $responseData = $response->getResponseData();

        $products = [];
        foreach ($responseData['products'] as $product) {
            $products[] = new Product(
                $product['identifier'],
                (string)$product['opc'],
                $product['name'],
                $product['url'],
                $product['img'],
            );
        }

        return new \M2E\OnBuy\Model\Channel\Connector\Product\Search\Response(
            $products
        );
    }
}
