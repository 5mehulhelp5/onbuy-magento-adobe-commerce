<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Type\Revise;

class Validator extends \M2E\OnBuy\Model\Product\Action\Type\AbstractValidator
{
    private \M2E\OnBuy\Model\Product\Action\Validator\PriceValidator $priceValidator;

    public function __construct(
        \M2E\OnBuy\Model\Product\Action\Validator\PriceValidator $priceValidator
    ) {
        $this->priceValidator = $priceValidator;
    }

    public function validate(): bool
    {
        if (!$this->getListingProduct()->isRevisable()) {
            $this->addMessage('Item is not Listed or not available');

            return false;
        }

        if (empty($this->getListingProduct()->getOnlineSku())) {
            return false;
        }

        if ($error = $this->priceValidator->validate($this->getListingProduct(), $this->getConfigurator())) {
            $this->addMessage($error);

            return false;
        }

        return true;
    }
}
