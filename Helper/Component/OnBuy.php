<?php

declare(strict_types=1);

namespace M2E\OnBuy\Helper\Component;

class OnBuy
{
    public const MAX_LENGTH_FOR_OPTION_VALUE = 50;

    /**
     * @param array $options
     *
     * @return array
     */
    public static function prepareOptionsForOrders(array $options): array
    {
        foreach ($options as &$singleOption) {
            if ($singleOption instanceof \Magento\Catalog\Model\Product) {
                $reducedName = trim(
                    \M2E\Core\Helper\Data::reduceWordsInString(
                        $singleOption->getName(),
                        self::MAX_LENGTH_FOR_OPTION_VALUE
                    )
                );
                $singleOption->setData('name', $reducedName);

                continue;
            }

            foreach ($singleOption['values'] as &$singleOptionValue) {
                foreach ($singleOptionValue['labels'] as &$singleOptionLabel) {
                    $singleOptionLabel = trim(
                        \M2E\Core\Helper\Data::reduceWordsInString(
                            $singleOptionLabel,
                            self::MAX_LENGTH_FOR_OPTION_VALUE
                        )
                    );
                }
            }
        }

        if (isset($options['additional']['attributes'])) {
            foreach ($options['additional']['attributes'] as $code => &$title) {
                $title = trim($title);
            }
            unset($title);
        }

        return $options;
    }
}
