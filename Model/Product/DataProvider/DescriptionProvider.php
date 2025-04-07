<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\DataProvider;

class DescriptionProvider implements DataBuilderInterface
{
    use DataBuilderHelpTrait;

    public const NICK = 'Description';
    private string $onlineDescription = '';

    public function getDescription(\M2E\OnBuy\Model\Product $product): Description\Value
    {
        $data = $product->getRenderedDescription();

        $this->onlineDescription = \M2E\Core\Helper\Data::md5String($data);

        return new Description\Value($data);
    }

    public function getMetaData(): array
    {
        return [
            self::NICK => ['online_description' => $this->onlineDescription],
        ];
    }
}
