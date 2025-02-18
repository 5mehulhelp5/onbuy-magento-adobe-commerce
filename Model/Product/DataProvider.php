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
