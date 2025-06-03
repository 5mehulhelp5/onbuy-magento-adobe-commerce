<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\DataProvider;

class TitleProvider implements DataBuilderInterface
{
    use DataBuilderHelpTrait;

    public const NICK = 'Title';

    /**
     * @param \M2E\OnBuy\Model\Product $product
     *
     * @return string
     * @throws \M2E\OnBuy\Model\Exception\Logic
     */
    public function getTitle(\M2E\OnBuy\Model\Product $product): string
    {
        $title = $product->getDescriptionTemplateSource()->getTitle();

        if (strlen($title) > 70) {
            $title = substr($title, 0, 70);
        }

        return $title;
    }
}
