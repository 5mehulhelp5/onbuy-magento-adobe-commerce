<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Type\ListAction;

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

        if (isset($data['product_url'])) {
            $product->setProductLinkOnChannel($data['product_url']);
        }

        if (isset($requestMetadata['delivery_template_id'])) {
            $product->setOnlineDeliveryTemplateId((int)$requestMetadata['delivery_template_id']);
        }

        if (isset($requestMetadata['title'])) {
            $product->setOnlineTitle($requestMetadata['title']);
        }

        if (isset($requestMetadata['description_hash'])) {
            $product->setOnlineDescription($requestMetadata['description_hash']);
        }

        if (isset($requestMetadata['main_image'])) {
            $product->setOnlineMainImage($requestMetadata['main_image']);
        }

        if (isset($requestMetadata['additional_images_hash'])) {
            $product->setOnlineAdditionalImages($requestMetadata['additional_images_hash']);
        }

        if (isset($requestMetadata['category_id'])) {
            $product->setOnlineCategoryId((int)$requestMetadata['category_id']);
        }

        if (isset($requestMetadata['attributes_hash'])) {
            $product->setOnlineCategoryAttributesData($requestMetadata['attributes_hash']);
        }

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

        $responseData = $this->getResponseData();
        foreach ($responseData['messages'] ?? [] as $messageData) {
            if ($messageData['type'] === \M2E\Core\Model\Response\Message::TYPE_WARNING) {
                $this->getLogBuffer()->addWarning($messageData['text']);
            }
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
