<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Type\Relist;

class ProcessEnd extends \M2E\OnBuy\Model\Product\Action\Async\AbstractProcessEnd
{
    private ResponseFactory $responseFactory;

    public function __construct(
        ResponseFactory $responseFactory
    ) {
        $this->responseFactory = $responseFactory;
    }

    protected function processComplete(array $resultData, array $messages): void
    {
        if (empty($resultData)) {
            return;
        }

        $this->processSuccess($resultData);
    }

    private function processSuccess(array $data): void
    {
        /** @var Response $responseObj */
        $responseObj = $this->responseFactory->create(
            $this->getListingProduct(),
            $this->getListingProduct()->getActionConfigurator(),
            $this->getLogBuffer(),
            $this->getParams(),
            $this->getStatusChanger(),
            $this->getRequestMetadata(),
            $data
        );

        $responseObj->process();
        $responseObj->generateResultMessage();
    }
}
