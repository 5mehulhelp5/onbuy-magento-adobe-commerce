<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Type\Revise;

class Validator extends \M2E\OnBuy\Model\Product\Action\Type\AbstractValidator
{
    /** @var \M2E\OnBuy\Model\Product\Action\Validator\ValidatorInterface[] */
    private array $validators;

    public function __construct(
        array $validators = []
    ) {
        $this->validators = $validators;
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

        foreach ($this->validators as $validator) {
            $error = $validator->validate($this->getListingProduct(), $this->getConfigurator());
            if ($error !== null) {
                $this->addMessage($error);
            }
        }

        return !$this->hasErrorMessages();
    }
}
