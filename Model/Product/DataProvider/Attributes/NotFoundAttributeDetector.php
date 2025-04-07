<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\DataProvider\Attributes;

class NotFoundAttributeDetector
{
    private \M2E\Core\Helper\Magento\Attribute $attributeHelper;
    /** @var string[] */
    private array $warningMessages = [];

    public function __construct(
        \M2E\Core\Helper\Magento\Attribute $attributeHelper
    ) {
        $this->attributeHelper = $attributeHelper;
    }

    public function clearMessages(): void
    {
        $this->warningMessages = [];
    }

    public function getWarningMessages(): array
    {
        return array_values($this->warningMessages);
    }

    public function searchNotFoundAttributes(\M2E\OnBuy\Model\Magento\Product $magentoProduct): void
    {
        $magentoProduct->clearNotFoundAttributes();
    }

    public function addWarningMessage(string $message): void
    {
        $this->warningMessages[sha1($message)] = $message;
    }

    public function processNotFoundAttributes(
        \M2E\OnBuy\Model\Magento\Product $magentoProduct,
        int $storeId,
        string $title
    ): void {
        $attributes = $magentoProduct->getNotFoundAttributes();
        if (!empty($attributes)) {
            $this->addNotFoundAttributesMessages($attributes, $storeId, $title);
        }
    }

    private function addNotFoundAttributesMessages(array $attributes, int $storeId, string $title): void
    {
        $attributesTitles = [];

        foreach ($attributes as $attribute) {
            $attributesTitles[] = $this->attributeHelper
                ->getAttributeLabel(
                    $attribute,
                    $storeId,
                );
        }

        $this->addWarningMessage(
            (string)__(
                '%1: Attribute(s) %2 were not found' .
                ' in this Product and its value was not sent.',
                $title,
                implode(', ', $attributesTitles),
            ),
        );
    }
}
