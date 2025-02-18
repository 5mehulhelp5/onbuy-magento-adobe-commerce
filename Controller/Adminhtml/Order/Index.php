<?php

namespace M2E\OnBuy\Controller\Adminhtml\Order;

class Index extends AbstractOrder
{
    public function execute()
    {
        $this->init();
        $this->addContent($this->getLayout()->createBlock(\M2E\OnBuy\Block\Adminhtml\Order\Order::class));
        $this->setPageHelpLink('https://docs-m2.m2epro.com/docs/m2e-onbuy-orders/');

        return $this->getResultPage();
    }
}
