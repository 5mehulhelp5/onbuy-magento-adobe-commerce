<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Connector\Attribute;

class GetCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $serverHash;
    private int $siteId;
    private int $categoryId;

    public function __construct(string $serverHash, int $siteId, int $categoryId)
    {
        $this->serverHash = $serverHash;
        $this->siteId = $siteId;
        $this->categoryId = $categoryId;
    }

    public function getCommand(): array
    {
        return ['category', 'get', 'attributes'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->serverHash,
            'site_id' => $this->siteId,
            'category_id' => $this->categoryId
        ];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): Get\Response
    {
        $this->processError($response);
        $responseData = $response->getResponseData();

        $attributes = [];
        foreach ($responseData['attributes'] as $attributeData) {
            $attribute = new \M2E\OnBuy\Model\Channel\Attribute\Item(
                (string)$attributeData['id'],
                $attributeData['name'],
                \M2E\OnBuy\Model\Channel\Attribute\Item::PRODUCT_TYPE,
                $attributeData['is_required']
            );

            foreach ($attributeData['options'] as $value) {
                $attribute->addValue(
                    (string)$value['id'],
                    $value['name']
                );
            }

            $attributes[] = $attribute;
        }

        return new \M2E\OnBuy\Model\Channel\Connector\Attribute\Get\Response(
            $attributes,
            []
        );
    }

    private function processError(\M2E\Core\Model\Connector\Response $response): void
    {
        if (!$response->isResultError()) {
            return;
        }

        foreach ($response->getMessageCollection()->getMessages() as $message) {
            if ($message->isError()) {
                throw new \M2E\OnBuy\Model\Exception\CategoryInvalid(
                    $message->getText(),
                    [],
                    (int)$message->getCode()
                );
            }
        }
    }
}
