<?php

namespace M2E\OnBuy\Controller\Adminhtml\General;

use M2E\OnBuy\Controller\Adminhtml\AbstractGeneral;

class GenerateAttributeCodeByLabel extends AbstractGeneral
{
    public function execute()
    {
        $label = $this->getRequest()->getParam('store_label');
        $this->setAjaxContent(\M2E\OnBuy\Model\Magento\Attribute\Builder::generateCodeByLabel($label), false);

        return $this->getResult();
    }
}
