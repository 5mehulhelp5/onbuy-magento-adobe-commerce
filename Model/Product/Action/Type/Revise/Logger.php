<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Type\Revise;

class Logger
{
    private array $logs = [];
    private \Magento\Framework\Locale\CurrencyInterface $localeCurrency;

    private float $onlinePrice;
    private int $onlineQty;

    public function __construct(
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency
    ) {
        $this->localeCurrency = $localeCurrency;
    }

    public function saveProductDataBeforeUpdate(\M2E\OnBuy\Model\Product $product): void
    {
        $this->onlinePrice = $product->getOnlinePrice();
        $this->onlineQty = $product->getOnlineQty();
    }

    public function collectSuccessMessages(\M2E\OnBuy\Model\Product $product): array
    {
        $this->generateMessageAboutChangePrice($product);
        $this->generateMessageAboutChangeQty($product);

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
}
