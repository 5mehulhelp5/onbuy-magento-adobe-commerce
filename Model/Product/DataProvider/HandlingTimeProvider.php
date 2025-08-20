<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\DataProvider;

class HandlingTimeProvider implements DataBuilderInterface
{
    use DataBuilderHelpTrait;

    public const NICK = 'HandlingTime';

    private \M2E\OnBuy\Model\Product\DataProvider\Attributes\NotFoundAttributeDetector $notFoundAttributeDetector;

    public function __construct(
        \M2E\OnBuy\Model\Product\DataProvider\Attributes\NotFoundAttributeDetector $notFoundAttributeDetector
    ) {
        $this->notFoundAttributeDetector = $notFoundAttributeDetector;
    }

    public function getHandlingTime(\M2E\OnBuy\Model\Product $product): ?int
    {
        $listing = $product->getListing();

        if (!$listing->hasTemplateShipping()) {
            return null;
        }

        $shippingPolicy = $product->getShippingTemplate();
        $mode = $shippingPolicy->getHandlingTimeMode();

        if ($mode === \M2E\OnBuy\Model\Policy\Shipping::HANDLING_TIME_MODE_NOT_SET) {
            return null;
        }

        if ($mode === \M2E\OnBuy\Model\Policy\Shipping::HANDLING_TIME_MODE_ATTRIBUTE) {
            return $this->getHandlingTimeFromAttribute($product, $shippingPolicy);
        }

        return $shippingPolicy->getHandlingTime();
    }

    private function getHandlingTimeFromAttribute(\M2E\OnBuy\Model\Product $product, \M2E\OnBuy\Model\Policy\Shipping $shippingPolicy): ?int
    {
        $magentoProduct = $product->getMagentoProduct();
        $this->notFoundAttributeDetector->clearMessages();
        $this->notFoundAttributeDetector->searchNotFoundAttributes($magentoProduct);

        $attributeValue = $magentoProduct->getAttributeValue($shippingPolicy->getHandlingTimeAttribute());

        if (empty($attributeValue)) {
            $this->notFoundAttributeDetector->processNotFoundAttributes(
                $magentoProduct,
                $product->getListing()->getStoreId(),
                (string)__('Handling Time')
            );

            foreach ($this->notFoundAttributeDetector->getWarningMessages() as $message) {
                $this->addWarningMessage($message);
            }

            return null;
        }

        if (empty((int)$attributeValue)) {
            $this->addWarningMessage((string)__('The specified handling time is invalid. The value was not sent to the channel.'));

            return null;
        }

        if ((int)$attributeValue > 10) {
            $this->addWarningMessage((string)__('The specified handling time is invalid - it should not exceed 10 days. The value was not sent to the channel.'));

            return null;
        }

        return (int)$attributeValue;
    }
}
