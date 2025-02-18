<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Type\ListAction;

class Validator extends \M2E\OnBuy\Model\Product\Action\Type\AbstractValidator
{
    private \M2E\OnBuy\Model\Product\Action\Validator\SameSkuAlreadyExists $sameSkuAlreadyExists;
    private \M2E\OnBuy\Model\Product\Action\Validator\PriceValidator $priceValidator;
    private \M2E\OnBuy\Model\Product\Action\Validator\SameOpcAndConditionExists $opcConditionExists;
    private \M2E\OnBuy\Model\Product\Action\Validator\QtyValidator $qtyValidator;
    private \M2E\OnBuy\Model\Product\Action\Validator\ConditionValidator $conditionValidator;

    public function __construct(
        \M2E\OnBuy\Model\Product\Action\Validator\SameSkuAlreadyExists $sameSkuAlreadyExists,
        \M2E\OnBuy\Model\Product\Action\Validator\PriceValidator $priceValidator,
        \M2E\OnBuy\Model\Product\Action\Validator\SameOpcAndConditionExists $opcConditionExists,
        \M2E\OnBuy\Model\Product\Action\Validator\QtyValidator $qtyValidator,
        \M2E\OnBuy\Model\Product\Action\Validator\ConditionValidator $conditionValidator
    ) {
        $this->priceValidator = $priceValidator;
        $this->sameSkuAlreadyExists = $sameSkuAlreadyExists;
        $this->opcConditionExists = $opcConditionExists;
        $this->qtyValidator = $qtyValidator;
        $this->conditionValidator = $conditionValidator;
    }

    public function validate(): bool
    {
        if (!$this->getListingProduct()->isListable()) {
            $this->addMessage((string)__('Item is Listed or not available'));

            return false;
        }

        if ($error = $this->sameSkuAlreadyExists->validate($this->getListingProduct())) {
            $this->addMessage($error);

            return false;
        }

        if ($error = $this->priceValidator->validate($this->getListingProduct())) {
            $this->addMessage($error);

            return false;
        }

        if ($error = $this->opcConditionExists->validate($this->getListingProduct())) {
            $this->addMessage($error);

            return false;
        }

        if ($error = $this->qtyValidator->validate($this->getListingProduct())) {
            $this->addMessage($error);

            return false;
        }

        if ($error = $this->conditionValidator->validate($this->getListingProduct())) {
            $this->addMessage($error);

            return false;
        }

        return !$this->hasErrorMessages();
    }
}
