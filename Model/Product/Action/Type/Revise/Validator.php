<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Type\Revise;

class Validator extends \M2E\OnBuy\Model\Product\Action\Type\AbstractValidator
{
    /** @var \M2E\OnBuy\Model\Product\Action\Validator\ValidatorInterface[] */
    private array $validators;

    /** @var \M2E\OnBuy\Model\Product\Action\Validator\ValidatorInterface[] */
    private array $validatorsProduct;

    public function __construct(
        array $validators = [],
        array $validatorsProduct = []
    ) {
        $this->validators = $validators;
        $this->validatorsProduct = $validatorsProduct;
    }

    public function validate(): bool
    {
        if (!$this->getListingProduct()->isRevisable()) {
            $this->addMessage(
                new \M2E\OnBuy\Model\Product\Action\Validator\ValidatorMessage(
                    (string)__('Item is not Listed or not available'),
                    \M2E\OnBuy\Model\Tag\ValidatorIssues::NOT_USER_ERROR
                )
            );

            return false;
        }

        if (empty($this->getListingProduct()->getOnlineSku())) {
            return false;
        }

        foreach ($this->validators as $validator) {
            $error = $validator->validate($this->getListingProduct());
            if ($error !== null) {
                $this->addMessage($error);
            }
        }

        if ($this->getListingProduct()->isProductCreator() && $this->getConfigurator()->isDetailsAllowed()) {
            foreach ($this->validatorsProduct as $validator) {
                $error = $validator->validate($this->getListingProduct());
                if ($error !== null) {
                    $this->addMessage($error);
                }
            }
        }

        return !$this->hasErrorMessages();
    }
}
