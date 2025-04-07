<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Type\Revise;

use M2E\OnBuy\Model\Product\DataProvider\PriceProvider;
use M2E\OnBuy\Model\Product\DataProvider\QtyProvider;

class Response extends \M2E\OnBuy\Model\Product\Action\Type\AbstractResponse
{
    private \M2E\OnBuy\Model\Product\Repository $productRepository;
    protected \Magento\Framework\Locale\CurrencyInterface $localeCurrency;
    private \M2E\OnBuy\Model\Product\Action\Type\Revise\LoggerFactory $loggerFactory;

    public function __construct(
        \M2E\OnBuy\Model\Product\Repository $productRepository,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \M2E\OnBuy\Model\Tag\ListingProduct\Buffer $tagBuffer,
        \M2E\OnBuy\Model\TagFactory $tagFactory,
        \M2E\OnBuy\Model\Product\Action\Type\Revise\LoggerFactory $loggerFactory
    ) {
        parent::__construct($tagBuffer, $tagFactory);

        $this->loggerFactory = $loggerFactory;
        $this->productRepository = $productRepository;
        $this->localeCurrency = $localeCurrency;
    }

    public function process(): void
    {
        $responseData = $this->getResponseData();
        if (!empty($responseData['messages'])) {
            $this->addTags($responseData['messages']);
        }

        $this->processSuccess();
    }

    protected function processSuccess(): void
    {
        $requestMetadata = $this->getRequestMetaData();
        $responseData = $this->getResponseData();

        $product = $this->getProduct();

        $productResponseData = $responseData['data'];

        $logger = $this->loggerFactory->create();
        $logger->saveProductDataBeforeUpdate($product);

        if (
            $this->isTriedUpdatePrice(
                isset($productResponseData['price']),
                isset($requestMetadata['price'])
            )
        ) {
            $priceUpdateStatus = $productResponseData['price'];
            $requestMetadataPrice = $requestMetadata['price'];
            if (!$priceUpdateStatus) {
                $this->getLogBuffer()->addFail('Price failed to be revised.');
            } else {
                $product->setOnlinePrice($requestMetadataPrice);
            }
        }

        if (
            $this->isTriedUpdateQty(
                isset($productResponseData['qty']),
                isset($requestMetadata['qty'])
            )
        ) {
            $qtyUpdateStatus = $productResponseData['qty'];
            $requestMetadataQty = $requestMetadata['qty'];
            if (!$qtyUpdateStatus) {
                $this->getLogBuffer()->addFail('Qty failed to be revised.');
            } else {
                $product->setOnlineQty($requestMetadataQty);
            }
        }

        $product->removeBlockingByError();

        $this->productRepository->save($product);

        $messages = $logger->collectSuccessMessages($product);
        if (empty($messages)) {
            $this->getLogBuffer()->addSuccess('Item was revised');
        }

        foreach ($messages as $message) {
            $this->getLogBuffer()->addSuccess($message);
        }
    }

    private function isTriedUpdatePrice(bool $isPricePresentInResponse, bool $isSendPrice): bool
    {
        return $isPricePresentInResponse && $isSendPrice;
    }

    private function isTriedUpdateQty(bool $isQtyPresentInResponse, bool $isSendQty): bool
    {
        return $isQtyPresentInResponse && $isSendQty;
    }

    public function generateResultMessage(): void
    {
        $responseData = $this->getResponseData();

        foreach ($responseData['messages'] ?? [] as $messageData) {
            $this->getLogBuffer()->addFail($messageData['text']);
        }
    }
}
