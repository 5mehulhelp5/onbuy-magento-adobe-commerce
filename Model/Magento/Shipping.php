<?php

namespace M2E\OnBuy\Model\Magento;

use Magento\Shipping\Model\Carrier\CarrierInterface;

class Shipping extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements CarrierInterface
{
    protected $_code = 'onbuyshipping';

    private \Magento\Shipping\Model\Rate\ResultFactory $resultFactory;
    private \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateResultMethodFactory;
    private \M2E\OnBuy\Helper\Data\GlobalData $globalDataHelper;

    public function __construct(
        \M2E\OnBuy\Helper\Data\GlobalData $globalDataHelper,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateResultMethodFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $resultFactory,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        $this->resultFactory = $resultFactory;
        $this->rateResultMethodFactory = $rateResultMethodFactory;
        $this->globalDataHelper = $globalDataHelper;
    }

    public function collectRates(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        $shippingData = $this->globalDataHelper->getValue('shipping_data');

        if (!$shippingData) {
            return false;
        }

        $result = $this->resultFactory->create();
        $method = $this->rateResultMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setMethod($this->_code);

        $method->setCarrierTitle($shippingData['carrier_title']);
        $method->setMethodTitle($shippingData['shipping_method']);

        $method->setCost($shippingData['shipping_price']);
        $method->setPrice($shippingData['shipping_price']);

        $result->append($method);

        return $result;
    }

    public function checkAvailableShipCountries(\Magento\Framework\DataObject $request)
    {
        if (!$this->globalDataHelper->getValue('shipping_data')) {
            return false;
        }

        return true;
    }

    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    public function isTrackingAvailable()
    {
        return false;
    }
}
