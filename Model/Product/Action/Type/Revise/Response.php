<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Type\Revise;

use M2E\OnBuy\Model\Product\DataProvider\PriceProvider;
use M2E\OnBuy\Model\Product\DataProvider\QtyProvider;

class Response extends \M2E\OnBuy\Model\Product\Action\Type\AbstractResponse
{
    private \M2E\OnBuy\Model\Product\Repository $productRepository;
    protected \Magento\Framework\Locale\CurrencyInterface $localeCurrency;

    public function __construct(
        \M2E\OnBuy\Model\Product\Repository $productRepository,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \M2E\OnBuy\Model\Tag\ListingProduct\Buffer $tagBuffer,
        \M2E\OnBuy\Model\TagFactory $tagFactory
    ) {
        parent::__construct($tagBuffer, $tagFactory);

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

        if (
            $this->isTriedUpdatePrice(
                isset($productResponseData['price']),
                isset($requestMetadata['price']) || isset($requestMetadata[PriceProvider::NICK]['price'])
            )
        ) {
            $priceUpdateStatus = $productResponseData['price'];
            $requestMetadataPrice = $requestMetadata['price'] ?? $requestMetadata[PriceProvider::NICK]['price'];
            if (!$priceUpdateStatus) {
                $this->getLogBuffer()->addFail('Price failed to be revised.');
            } else {
                $message = $this->generateMessageAboutChangePrice($product, $requestMetadataPrice);
                if ($message !== null) {
                    $this->getLogBuffer()->addSuccess($message);
                }

                $product->setOnlinePrice($requestMetadataPrice);
            }
        }

        if (
            $this->isTriedUpdateQty(
                isset($productResponseData['qty']),
                isset($requestMetadata['qty']) || isset($requestMetadata[QtyProvider::NICK]['qty'])
            )
        ) {
            $qtyUpdateStatus = $productResponseData['qty'];
            $requestMetadataQty = $requestMetadata['qty'] ?? $requestMetadata[QtyProvider::NICK]['qty'];
            if (!$qtyUpdateStatus) {
                $this->getLogBuffer()->addFail('Qty failed to be revised.');
            } else {
                $message = $this->generateMessageAboutChangeQty($product, $requestMetadataQty);
                if ($message !== null) {
                    $this->getLogBuffer()->addSuccess($message);
                }

                $product->setOnlineQty($requestMetadataQty);
            }
        }

        $product->removeBlockingByError();

        $this->productRepository->save($product);
    }

    private function isTriedUpdatePrice(bool $isPricePresentInResponse, bool $isSendPrice): bool
    {
        return $isPricePresentInResponse && $isSendPrice;
    }

    private function isTriedUpdateQty(bool $isQtyPresentInResponse, bool $isSendQty): bool
    {
        return $isQtyPresentInResponse && $isSendQty;
    }

    private function generateMessageAboutChangePrice(\M2E\OnBuy\Model\Product $product, float $to): ?string
    {
        $from = $product->getOnlinePrice();
        if ($from === $to) {
            return null;
        }

        $currencyCode = $product->getCurrencyCode();
        $currency = $this->localeCurrency->getCurrency($currencyCode);

        return sprintf(
            'Price was revised from %s to %s',
            $currency->toCurrency($from),
            $currency->toCurrency($to)
        );
    }

    private function generateMessageAboutChangeQty(\M2E\OnBuy\Model\Product $product, int $to): ?string
    {
        $from = $product->getOnlineQty();
        if ($from === $to) {
            return null;
        }

        return sprintf('QTY was revised from %s to %s', $from, $to);
    }

    public function generateResultMessage(): void
    {
        $responseData = $this->getResponseData();

        foreach ($responseData['messages'] ?? [] as $messageData) {
            $this->getLogBuffer()->addFail($messageData['text']);
        }
    }
}
