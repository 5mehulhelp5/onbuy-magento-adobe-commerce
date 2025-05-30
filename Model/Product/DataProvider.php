<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product;

class DataProvider
{
    /** @var \M2E\OnBuy\Model\Product\DataProvider\DataBuilderInterface[] */
    private array $dataBuilders = [];

    /** @var \M2E\OnBuy\Model\Product\DataProvider\AbstractResult[] */
    private array $results = [];

    /** @var \M2E\OnBuy\Model\Product\DataProvider\Factory */
    private \M2E\OnBuy\Model\Product\DataProvider\Factory $dataBuilderFactory;

    private \M2E\OnBuy\Model\Product $product;

    public function __construct(
        \M2E\OnBuy\Model\Product $product,
        \M2E\OnBuy\Model\Product\DataProvider\Factory $dataBuilderFactory
    ) {
        $this->product = $product;
        $this->dataBuilderFactory = $dataBuilderFactory;
    }

    // ----------------------------------------

    public function getPrice(): DataProvider\Price\Result
    {
        if ($this->hasResult(DataProvider\PriceProvider::NICK)) {
            /** @var DataProvider\Price\Result */
            return $this->getResult(DataProvider\PriceProvider::NICK);
        }

        /** @var \M2E\OnBuy\Model\Product\DataProvider\PriceProvider $builder */
        $builder = $this->getBuilder(\M2E\OnBuy\Model\Product\DataProvider\PriceProvider::NICK);

        $value = $builder->getPrice($this->product);

        $result = DataProvider\Price\Result::success($value);

        $this->addResult(DataProvider\PriceProvider::NICK, $result);

        return $result;
    }

    public function getQty(): DataProvider\Qty\Result
    {
        if ($this->hasResult(DataProvider\QtyProvider::NICK)) {
            /** @var DataProvider\Qty\Result */
            return $this->getResult(DataProvider\QtyProvider::NICK);
        }

        /** @var \M2E\OnBuy\Model\Product\DataProvider\QtyProvider $builder */
        $builder = $this->getBuilder(\M2E\OnBuy\Model\Product\DataProvider\QtyProvider::NICK);

        $value = $builder->getQty($this->product);

        $result = DataProvider\Qty\Result::success($value, $builder->getWarningMessages());

        $this->addResult(DataProvider\QtyProvider::NICK, $result);

        return $result;
    }

    public function getDelivery(): DataProvider\Delivery\Result
    {
        if ($this->hasResult(DataProvider\DeliveryProvider::NICK)) {
            /** @var DataProvider\Delivery\Result */
            return $this->getResult(DataProvider\DeliveryProvider::NICK);
        }

        /** @var \M2E\OnBuy\Model\Product\DataProvider\DeliveryProvider $builder */
        $builder = $this->getBuilder(\M2E\OnBuy\Model\Product\DataProvider\DeliveryProvider::NICK);

        $value = $builder->getDeliveryTemplateId($this->product);

        $result = DataProvider\Delivery\Result::success($value, $builder->getWarningMessages());

        $this->addResult(DataProvider\DeliveryProvider::NICK, $result);

        return $result;
    }

    public function getTitle(): DataProvider\Title\Result
    {
        if ($this->hasResult(DataProvider\TitleProvider::NICK)) {
            /** @var DataProvider\Title\Result */
            return $this->getResult(DataProvider\TitleProvider::NICK);
        }

        /** @var \M2E\OnBuy\Model\Product\DataProvider\TitleProvider $builder */
        $builder = $this->getBuilder(\M2E\OnBuy\Model\Product\DataProvider\TitleProvider::NICK);

        $title = $builder->getTitle($this->product);

        $result = DataProvider\Title\Result::success($title);

        $this->addResult(DataProvider\TitleProvider::NICK, $result);

        return $result;
    }

    public function getDescription(): DataProvider\Description\Result
    {
        if ($this->hasResult(DataProvider\DescriptionProvider::NICK)) {
            /** @var DataProvider\Description\Result */
            return $this->getResult(DataProvider\DescriptionProvider::NICK);
        }

        /** @var \M2E\OnBuy\Model\Product\DataProvider\DescriptionProvider $builder */
        $builder = $this->getBuilder(\M2E\OnBuy\Model\Product\DataProvider\DescriptionProvider::NICK);

        $value = $builder->getDescription($this->product);

        $result = DataProvider\Description\Result::success($value);

        $this->addResult(DataProvider\DescriptionProvider::NICK, $result);

        return $result;
    }

    public function getImages(): DataProvider\Images\Result
    {
        if ($this->hasResult(DataProvider\ImagesProvider::NICK)) {
            /** @var DataProvider\Images\Result */
            return $this->getResult(DataProvider\ImagesProvider::NICK);
        }

        /** @var \M2E\OnBuy\Model\Product\DataProvider\ImagesProvider $builder */
        $builder = $this->getBuilder(\M2E\OnBuy\Model\Product\DataProvider\ImagesProvider::NICK);

        $value = $builder->getImages($this->product);

        $result = DataProvider\Images\Result::success($value);

        $this->addResult(DataProvider\ImagesProvider::NICK, $result);

        return $result;
    }

    public function getIdentifier(): DataProvider\Identifier\Result
    {
        if ($this->hasResult(DataProvider\IdentifierProvider::NICK)) {
            /** @var \M2E\OnBuy\Model\Product\DataProvider\Identifier\Result */
            return $this->getResult(DataProvider\IdentifierProvider::NICK);
        }

        /** @var \M2E\OnBuy\Model\Product\DataProvider\IdentifierProvider $builder */
        $builder = $this->getBuilder(DataProvider\IdentifierProvider::NICK);

        $value = $builder->getIdentifier($this->product);

        $result = DataProvider\Identifier\Result::success($value, $builder->getWarningMessages());

        $this->addResult(DataProvider\IdentifierProvider::NICK, $result);

        return $result;
    }

    public function getCategoryData(): DataProvider\Category\Result
    {
        if ($this->hasResult(DataProvider\CategoryProvider::NICK)) {
            /** @var DataProvider\Category\Result */
            return $this->getResult(DataProvider\CategoryProvider::NICK);
        }

        /** @var \M2E\OnBuy\Model\Product\DataProvider\CategoryProvider $builder */
        $builder = $this->getBuilder(\M2E\OnBuy\Model\Product\DataProvider\CategoryProvider::NICK);
        $value = $builder->getCategoryData($this->product);
        $result = DataProvider\Category\Result::success($value);

        $this->addResult(DataProvider\CategoryProvider::NICK, $result);

        return $result;
    }

    public function getProductAttributesData(): DataProvider\Attributes\Result
    {
        if ($this->hasResult(DataProvider\ProductAttributesProvider::NICK)) {
            /** @var DataProvider\Attributes\Result */
            return $this->getResult(DataProvider\ProductAttributesProvider::NICK);
        }

        /** @var \M2E\OnBuy\Model\Product\DataProvider\ProductAttributesProvider $builder */
        $builder = $this->getBuilder(\M2E\OnBuy\Model\Product\DataProvider\ProductAttributesProvider::NICK);
        $value = $builder->getProductAttributesData($this->product);
        $result = DataProvider\Attributes\Result::success($value);

        $this->addResult(DataProvider\ProductAttributesProvider::NICK, $result);

        return $result;
    }

    public function getProductBrand(): DataProvider\Brand\Result
    {
        if ($this->hasResult(DataProvider\BrandProvider::NICK)) {
            /** @var DataProvider\Brand\Result */
            return $this->getResult(DataProvider\BrandProvider::NICK);
        }

        /** @var \M2E\OnBuy\Model\Product\DataProvider\BrandProvider $builder */
        $builder = $this->getBuilder(\M2E\OnBuy\Model\Product\DataProvider\BrandProvider::NICK);
        $value = $builder->getProductBrand($this->product);
        $result = ($value === null)
            ? DataProvider\Brand\Result::error($builder->getWarningMessages())
            : DataProvider\Brand\Result::success($value);

        $this->addResult(DataProvider\BrandProvider::NICK, $result);

        return $result;
    }

    /**
     * @return string[]
     */
    public function getLogs(): array
    {
        $result = [];
        foreach ($this->dataBuilders as $dataBuilder) {
            $message = $dataBuilder->getWarningMessages();
            if (empty($message)) {
                continue;
            }

            array_push($result, ...$message);
        }

        return $result;
    }

    // ----------------------------------------

    private function getBuilder(
        string $nick
    ): \M2E\OnBuy\Model\Product\DataProvider\DataBuilderInterface {
        if (isset($this->dataBuilders[$nick])) {
            return $this->dataBuilders[$nick];
        }

        return $this->dataBuilders[$nick] = $this->dataBuilderFactory->create($nick);
    }

    private function addResult(string $builderNick, DataProvider\AbstractResult $result): void
    {
        $this->results[$builderNick] = $result;
    }

    private function hasResult(string $builderNick): bool
    {
        return isset($this->results[$builderNick]);
    }

    private function getResult(string $builderNick): \M2E\OnBuy\Model\Product\DataProvider\AbstractResult
    {
        return $this->results[$builderNick];
    }
}
