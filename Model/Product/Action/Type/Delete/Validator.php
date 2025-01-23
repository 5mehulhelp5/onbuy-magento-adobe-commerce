<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Type\Delete;

class Validator extends \M2E\OnBuy\Model\Product\Action\Type\AbstractValidator
{
    private \M2E\OnBuy\Model\Product\RemoveHandler $removeHandler;

    public function __construct(
        \M2E\OnBuy\Model\Product\RemoveHandler $removeHandler
    ) {
        $this->removeHandler = $removeHandler;
    }

    public function validate(): bool
    {
        if (!$this->getListingProduct()->isRemovableFromChannel()) {
            $this->removeHandler->process($this->getListingProduct());

            return false;
        }

        return true;
    }
}
