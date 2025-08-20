<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Type\Revise;

class Logger
{
    private array $logs = [];
    private \Magento\Framework\Locale\CurrencyInterface $localeCurrency;

    private float $onlinePrice;
    private int $onlineQty;
    private ?int $onlineDeliveryTemplateId;
    private string $onlineTitle;
    private string $onlineDescription;
    private ?int $onlineCategoryId;
    private string $onlineCategoryAttributesData;
    private string $onlineMainImage;
    private string $onlineAdditionalImages;
    private ?int $onlineHandlingTime;

    public function __construct(
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency
    ) {
        $this->localeCurrency = $localeCurrency;
    }

    public function saveProductDataBeforeUpdate(\M2E\OnBuy\Model\Product $product): void
    {
        $this->onlinePrice = $product->getOnlinePrice();
        $this->onlineQty = $product->getOnlineQty();
        $this->onlineDeliveryTemplateId = $product->getOnlineDeliveryTemplateId();
        $this->onlineTitle = $product->getOnlineTitle();
        $this->onlineDescription = $product->getOnlineDescription();
        $this->onlineCategoryId = $product->getOnlineCategoryId();
        $this->onlineCategoryAttributesData = $product->getOnlineCategoryAttributesData();
        $this->onlineMainImage = $product->getOnlineMainImage();
        $this->onlineAdditionalImages = $product->getOnlineAdditionalImages();
        $this->onlineHandlingTime = $product->getOnlineHandlingTime();
    }

    public function collectSuccessMessages(\M2E\OnBuy\Model\Product $product): array
    {
        $this->generateMessageAboutChangePrice($product);
        $this->generateMessageAboutChangeQty($product);
        $this->generateMessageAboutChangeDeliveryTemplateId($product);
        $this->generateMessageAboutChangeTitle($product);
        $this->generateMessageAboutChangeDescription($product);
        $this->generateMessageAboutChangeCategories($product);
        $this->generateMessageAboutChangeImages($product);
        $this->generateMessageAboutChangeHandlingTime($product);

        return $this->logs;
    }

    private function generateMessageAboutChangePrice(\M2E\OnBuy\Model\Product $product): void
    {
        $from = $this->onlinePrice;
        $to = $product->getOnlinePrice();
        if ($from === $to) {
            return;
        }

        $currencyCode = $product->getCurrencyCode();
        $currency = $this->localeCurrency->getCurrency($currencyCode);

        $this->logs[] = sprintf(
            'Product Price was revised from %s to %s',
            $currency->toCurrency($from),
            $currency->toCurrency($to)
        );
    }

    private function generateMessageAboutChangeQty(\M2E\OnBuy\Model\Product $product): void
    {
        $from = $this->onlineQty;
        $to = $product->getOnlineQty();
        if ($from === $to) {
            return;
        }

        $this->logs[] =  sprintf('Product QTY was revised from %s to %s', $from, $to);
    }

    private function generateMessageAboutChangeDeliveryTemplateId(\M2E\OnBuy\Model\Product $product): void
    {
        if ($this->onlineDeliveryTemplateId !== $product->getOnlineDeliveryTemplateId()) {
            $this->logs[] = 'Item was revised: Shipping was updated.';
        }
    }

    private function generateMessageAboutChangeTitle(\M2E\OnBuy\Model\Product $product): void
    {
        if ($this->onlineTitle !== $product->getOnlineTitle()) {
            $this->logs[] = 'Item was revised: Product Title was updated.';
        }
    }

    private function generateMessageAboutChangeDescription(\M2E\OnBuy\Model\Product $product): void
    {
        if ($this->onlineDescription !== $product->getOnlineDescription()) {
            $this->logs[] = 'Item was revised: Product Description was updated.';
        }
    }

    private function generateMessageAboutChangeCategories(\M2E\OnBuy\Model\Product $product): void
    {
        if (
            $this->onlineCategoryId !== $product->getOnlineCategoryId()
            || $this->onlineCategoryAttributesData !== $product->getOnlineCategoryAttributesData()
        ) {
            $this->logs[] = 'Item was revised: Product Categories were updated.';
        }
    }

    private function generateMessageAboutChangeImages(\M2E\OnBuy\Model\Product $product): void
    {
        if (
            $this->onlineMainImage !== $product->getOnlineMainImage()
            || $this->onlineAdditionalImages !== $product->getOnlineAdditionalImages()
        ) {
            $this->logs[] = 'Item was revised: Product Images were updated.';
        }
    }

    private function generateMessageAboutChangeHandlingTime(\M2E\OnBuy\Model\Product $product): void
    {
        if ($this->onlineHandlingTime !== $product->getOnlineHandlingTime()) {
            $this->logs[] = 'Item was revised: Handling Time was updated.';
        }
    }
}
