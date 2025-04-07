<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Connector\Category;

class GetCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private const RESPONSE_CATEGORIES_KEY = 'categories';

    private int $siteId;

    public function __construct(int $siteId)
    {
        $this->siteId = $siteId;
    }

    public function getCommand(): array
    {
        return ['category', 'get', 'list'];
    }

    public function getRequestData(): array
    {
        return [
            'site_id' => $this->siteId
        ];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): Get\Response
    {
        $this->processError($response);

        $result = new Get\Response();
        $responseData = $response->getResponseData();

        foreach ($responseData[self::RESPONSE_CATEGORIES_KEY] as $categoryData) {
            $result->addCategory(
                new \M2E\OnBuy\Model\Channel\Category\Item(
                    $categoryData['id'],
                    $categoryData['name'],
                    $categoryData['is_leaf'],
                    $categoryData['parent_id']
                )
            );
        }

        return $result;
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
