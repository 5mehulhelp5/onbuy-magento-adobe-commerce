<?php

namespace M2E\OnBuy\Model\Magento\Tax;

use M2E\OnBuy\Model\Magento\Tax\Rule\Builder;

class Helper
{
    private \Magento\Tax\Model\Calculation\RateFactory $calculationRateFactory;
    private \Magento\Tax\Model\Config $taxConfig;
    private \Magento\Tax\Model\Calculation $taxCalculation;
    private \Magento\Store\Model\StoreManagerInterface $storeManager;

    public function __construct(
        \Magento\Tax\Model\Calculation\RateFactory $calculationRateFactory,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Tax\Model\Calculation $taxCalculation
    ) {
        $this->calculationRateFactory = $calculationRateFactory;
        $this->taxConfig = $taxConfig;
        $this->taxCalculation = $taxCalculation;
        $this->storeManager = $storeManager;
    }

    public function hasRatesForCountry($countryId)
    {
        return $this->calculationRateFactory->create()
                                            ->getCollection()
                                            ->addFieldToFilter('tax_country_id', $countryId)
                                            ->addFieldToFilter('code', ['neq' => Builder::TAX_RATE_CODE_PRODUCT])
                                            ->addFieldToFilter('code', ['neq' => Builder::TAX_RATE_CODE_SHIPPING])
                                            ->getSize();
    }

    /**
     * Return store tax rate for shipping
     *
     * @param \Magento\Store\Model\Store $store
     *
     * @return float
     */
    public function getStoreShippingTaxRate($store)
    {
        $request = new \Magento\Framework\DataObject();
        $request->setProductClassId($this->taxConfig->getShippingTaxClass($store));

        return $this->taxCalculation->getStoreRate($request, $store);
    }

    public function isCalculationBasedOnOrigin($store): bool
    {
        return $this->storeManager
                ->getStore($store)
                ->getConfig(\Magento\Tax\Model\Config::CONFIG_XML_PATH_BASED_ON) === 'origin';
    }
}
