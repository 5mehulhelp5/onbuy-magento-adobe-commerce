<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Type\ListAction;

use M2E\OnBuy\Model\Product\DataProvider\DeliveryProvider;
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
        if (!$this->isSuccess()) {
            $this->processFail();

            return;
        }

        $this->processSuccess();
    }

    //@todo Consider refactoring to get rid of arrays
    protected function processSuccess(): void
    {
        $requestMetadata = $this->getRequestMetaData();
        $responseData = $this->getResponseData();
        $data = $responseData['data'];
        $product = $this->getProduct();

        $product
            ->setOpc($data['opc'])
            ->setChannelProductId((int)$data['product_listing_id'])
            ->setOnlineQty($requestMetadata['qty'])
            ->setOnlinePrice($requestMetadata['price'])
            ->setOnlineSku($requestMetadata['sku'])
            ->setOnlineGroupSku($requestMetadata['sku'])
            ->setStatus(\M2E\OnBuy\Model\Product::STATUS_LISTED, $this->getStatusChanger())
            ->removeBlockingByError();

        $this->productRepository->save($product);
    }

    private function isSuccess(): bool
    {
        $responseData = $this->getResponseData();

        return $responseData['status'] === true;
    }

    private function processFail(): void
    {
        $responseData = $this->getResponseData();
        $messages = $responseData['messages'] ?? [];

        if (!empty($messages)) {
            $this->addTags($messages);

            foreach ($responseData['messages'] as $message) {
                $this->getLogBuffer()->addFail($message['text']);
            }
        }
    }

    public function generateResultMessage(): void
    {
        if (!$this->isSuccess()) {
            $this->getLogBuffer()->addFail('Product failed to be listed.');

            return;
        }

        $currencyCode = $this->getProduct()->getListing()->getSite()->getCurrencyCode();
        $currency = $this->localeCurrency->getCurrency($currencyCode);
        $onlineQty = $this->getProduct()->getOnlineQty();
        $onlinePrice = $this->getProduct()->getOnlinePrice();

        $message = sprintf(
            'Product was Listed with QTY %d, Price %s',
            $onlineQty,
            $currency->toCurrency($onlinePrice),
        );

        $this->getLogBuffer()->addSuccess($message);
    }
}
