<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Type\ListAction;

class Validator extends \M2E\OnBuy\Model\Product\Action\Type\AbstractValidator
{
    /** @var \M2E\OnBuy\Model\Product\Action\Validator\ValidatorInterface[] */
    private array $validatorsListing;

    /** @var \M2E\OnBuy\Model\Product\Action\Validator\ValidatorInterface[] */
    private array $validatorsProduct;

    public function __construct(
        array $validatorsListing = [],
        array $validatorsProduct = []
    ) {
        $this->validatorsListing = $validatorsListing;
        $this->validatorsProduct = $validatorsProduct;
    }

    public function validate(): bool
    {
        if (!$this->getListingProduct()->isListable()) {
            $this->addMessage((string)__('Item is Listed or not available'));

            return false;
        }

        foreach ($this->validatorsListing as $validator) {
            $error = $validator->validate($this->getListingProduct(), $this->getConfigurator());
            if ($error !== null) {
                $this->addMessage($error);
            }
        }

        if (!$this->getListingProduct()->hasOpc()) {
            foreach ($this->validatorsProduct as $validator) {
                $error = $validator->validate($this->getListingProduct(), $this->getConfigurator());
                if ($error !== null) {
                    $this->addMessage($error);
                }
            }
        }

        return !$this->hasErrorMessages();
    }
}
