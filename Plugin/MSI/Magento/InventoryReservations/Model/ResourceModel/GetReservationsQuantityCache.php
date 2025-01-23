<?php

namespace M2E\OnBuy\Plugin\MSI\Magento\InventoryReservations\Model\ResourceModel;

use Magento\InventoryReservations\Model\ResourceModel\GetReservationsQuantity;

class GetReservationsQuantityCache extends \M2E\OnBuy\Plugin\AbstractPlugin
{
    /** @var GetReservationsQuantity */
    private $getReservationsQuantity;
    private \M2E\OnBuy\Helper\Data\GlobalData $globalDataHelper;

    public function __construct(
        \M2E\OnBuy\Helper\Data\GlobalData $globalDataHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->getReservationsQuantity = $objectManager->get(GetReservationsQuantity::class);
        $this->globalDataHelper = $globalDataHelper;
    }

    public function aroundExecute($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('execute', $interceptor, $callback, $arguments);
    }

    public function processExecute($interceptor, \Closure $callback, array $arguments)
    {
        [$sku, $stockId] = $arguments;
        $key = 'released_reservation_product_' . $sku . '_' . $stockId;
        if ($this->globalDataHelper->getValue($key)) {
            return $this->getReservationsQuantity->execute($sku, $stockId);
        }

        return $callback(...$arguments);
    }

    //########################################
}
