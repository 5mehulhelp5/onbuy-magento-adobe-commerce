<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Type\Relist;

class Response extends \M2E\OnBuy\Model\Product\Action\Type\Revise\Response
{
    public function processExpire(): void
    {
        // do nothing
    }

    protected function processSuccess(): void
    {
        $product = $this->getProduct();
        $product->setStatus(\M2E\OnBuy\Model\Product::STATUS_LISTED, $this->getStatusChanger());

        parent::processSuccess();
    }

    /**
     * @throws \Magento\Framework\Currency\Exception\CurrencyException
     */
    public function generateResultMessage(): void
    {
        if (!$this->isProcessSuccess()) {
            $responseData = $this->getResponseData();
            if (empty($responseData['messages'])) {
                $this->getLogBuffer()->addFail('Product failed to be relisted.');

                return;
            }

            $firstMessage = reset($responseData['messages']);

            $resultMessage = sprintf(
                'Product failed to be relisted. Reason: %s',
                $firstMessage['text']
            );

            $this->getLogBuffer()->addFail($resultMessage);

            return;
        }

        $domainListingProduct = $this->getProduct();
        $onlineQty = $domainListingProduct->getOnlineQty();

        $currencyCode = $this->getProduct()->getCurrencyCode();
        $currency = $this->localeCurrency->getCurrency($currencyCode);

        $message = sprintf(
            'Product was Relisted with QTY %d, Price %s',
            $onlineQty,
            $currency->toCurrency($domainListingProduct->getOnlinePrice())
        );

        $this->getLogBuffer()->addSuccess($message);
    }
}
