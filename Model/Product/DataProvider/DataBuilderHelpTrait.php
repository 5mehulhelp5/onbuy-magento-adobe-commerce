<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\DataProvider;

trait DataBuilderHelpTrait
{
    /** @var string[] */
    private array $warningMessages = [];

    private function addWarningMessage(string $message): void
    {
        $this->warningMessages[sha1($message)] = $message;
    }

    public function getWarningMessages(): array
    {
        return array_values($this->warningMessages);
    }

    private function searchNotFoundAttributes(\M2E\OnBuy\Model\Magento\Product $magentoProduct): void
    {
        $magentoProduct->clearNotFoundAttributes();
    }
}
