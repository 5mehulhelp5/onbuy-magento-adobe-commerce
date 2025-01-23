<?php

namespace M2E\OnBuy\Controller\Adminhtml;

use M2E\OnBuy\Controller\Adminhtml\AbstractMain;

abstract class AbstractTemplate extends AbstractMain
{
    protected \M2E\OnBuy\Model\Policy\Manager $templateManager;

    public function __construct(
        \M2E\OnBuy\Model\Policy\Manager $templateManager
    ) {
        parent::__construct();
        $this->templateManager = $templateManager;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_OnBuy::configuration_templates');
    }
}
