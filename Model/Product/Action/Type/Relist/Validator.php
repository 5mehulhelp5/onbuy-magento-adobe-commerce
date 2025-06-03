<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Type\Relist;

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
        if (!$this->getListingProduct()->isRelistable()) {
            $this->addMessage(
                new \M2E\OnBuy\Model\Product\Action\Validator\ValidatorMessage(
                    (string)__('The Item either is Listed, or not Listed yet or not available'),
                    \M2E\OnBuy\Model\Tag\ValidatorIssues::NOT_USER_ERROR
                )
            );

            return false;
        }

        foreach ($this->validators as $validator) {
            $error = $validator->validate($this->getListingProduct());
            if ($error !== null) {
                $this->addMessage($error);
            }
        }

        return !$this->hasErrorMessages();
    }
}
