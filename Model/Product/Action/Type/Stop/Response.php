<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Type\Stop;

class Response extends \M2E\OnBuy\Model\Product\Action\Type\AbstractResponse
{
    private \M2E\OnBuy\Model\Product\Repository $productRepository;

    public function __construct(
        \M2E\OnBuy\Model\Product\Repository $productRepository,
        \M2E\OnBuy\Model\Tag\ListingProduct\Buffer $tagBuffer,
        \M2E\OnBuy\Model\TagFactory $tagFactory
    ) {
        parent::__construct($tagBuffer, $tagFactory);

        $this->productRepository = $productRepository;
    }

    public function process(): void
    {
        if (!$this->isSuccess()) {
            $this->processFail();

            return;
        }

        $this->processSuccess();
    }

    private function isSuccess(): bool
    {
        $responseData = $this->getResponseData();

        return $responseData['status'] === true;
    }

    public function processSuccess(): void
    {
        $this->getProduct()->setStatusInactive($this->getStatusChanger());

        $this->productRepository->save($this->getProduct());
    }

    private function processFail(): void
    {
        $responseData = $this->getResponseData();
        foreach ($responseData['messages'] as $message) {
            $this->getLogBuffer()->addFail($message['text']);
        }
    }

    public function generateResultMessage(): void
    {
        if (!$this->isSuccess()) {
            $this->getLogBuffer()->addFail('Product failed to be stopped.');

            return;
        }

        $this->getLogBuffer()->addSuccess('Item was Stopped');
    }
}
