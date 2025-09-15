<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Type\Revise;

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
        if (!$this->isProcessSuccess()) {
            $this->addTags($responseData['messages']);

            return;
        }

        $this->processSuccess();
    }

    protected function isProcessSuccess(): bool
    {
        $response = $this->getResponseData();

        return $response['status'];
    }

    public function processExpire(): void
    {
        // do nothing
    }

    protected function processSuccess(): void
    {
        $requestMetadata = $this->getRequestMetaData();
        $responseData = $this->getResponseData();

        $product = $this->getProduct();

        $productResponseData = $responseData['data'];

        $isPriceProcessed = isset($productResponseData['price']);
        $priceUpdateStatus = $productResponseData['price'] ?? false;

        $isQtyProcessed = isset($productResponseData['qty']);
        $qtyUpdateStatus = $productResponseData['qty'] ?? false;

        $logger = $this->loggerFactory->create();
        $logger->saveProductDataBeforeUpdate($product);

        if ($this->isTriedUpdatePrice($isPriceProcessed, isset($requestMetadata['price']))) {
            $requestMetadataPrice = $requestMetadata['price'];
            if (!$priceUpdateStatus) {
                $this->getLogBuffer()->addFail('Price failed to be revised.');
            } else {
                $product->setOnlinePrice($requestMetadataPrice);
            }
        }

        if ($this->isTriedUpdateQty($isQtyProcessed, isset($requestMetadata['qty']))) {
            $requestMetadataQty = $requestMetadata['qty'];
            if (!$qtyUpdateStatus) {
                $this->getLogBuffer()->addFail('Qty failed to be revised.');
            } else {
                $product->setOnlineQty($requestMetadataQty);
            }
        }

        if (isset($requestMetadata['delivery_template_id'])) {
            $shippingUpdateStatus = $priceUpdateStatus || $qtyUpdateStatus;
            $requestMetadataShipping = (int)$requestMetadata['delivery_template_id'];
            if (!$shippingUpdateStatus) {
                $this->getLogBuffer()->addFail('Shipping failed to be revised.');
            } else {
                $product->setOnlineDeliveryTemplateId($requestMetadataShipping);
            }
        }

        if (isset($requestMetadata['handling_time'])) {
            $shippingUpdateStatus = $priceUpdateStatus || $qtyUpdateStatus;
            $requestMetadataHandlingTime = (int)$requestMetadata['handling_time'];
            if (!$shippingUpdateStatus) {
                $this->getLogBuffer()->addFail('Handling Time failed to be revised.');
            } else {
                $product->setOnlineHandlingTime($requestMetadataHandlingTime);
            }
        }

        if (
            $this->isTriedUpdateDetails(
                isset($productResponseData['details']),
                isset($requestMetadata['details'])
            )
        ) {
            $detailUpdateStatus = $productResponseData['details'];
            if (!$detailUpdateStatus) {
                $this->getLogBuffer()->addFail('Details failed to be revised.');
            } else {
                $product->setOnlineTitle($requestMetadata['details']['title']);
                $product->setOnlineDescription($requestMetadata['details']['description_hash']);
                $product->setOnlineCategoryId((int)$requestMetadata['details']['category_id']);
                $product->setOnlineCategoryAttributesData($requestMetadata['details']['attributes_hash']);
                $product->setOnlineMainImage($requestMetadata['details']['main_image']);
                $product->setOnlineAdditionalImages($requestMetadata['details']['additional_images_hash']);
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

    private function isTriedUpdateDetails(bool $isDetailPresentInResponse, bool $isSendDetail): bool
    {
        return $isDetailPresentInResponse && $isSendDetail;
    }

    public function generateResultMessage(): void
    {
        $responseData = $this->getResponseData();

        foreach ($responseData['messages'] ?? [] as $messageData) {
            $this->getLogBuffer()->addFail($messageData['text']);
        }
    }
}
