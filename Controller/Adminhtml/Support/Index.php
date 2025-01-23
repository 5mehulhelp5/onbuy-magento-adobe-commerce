<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Support;

class Index extends \M2E\OnBuy\Controller\Adminhtml\AbstractBase
{
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('M2E_OnBuy::help_center_m2e_support');
    }

    public function execute()
    {
        $this->addContent(
            $this->getLayout()->createBlock(\M2E\OnBuy\Block\Adminhtml\Support::class)
        );
        $this->getResultPage()->getConfig()->getTitle()->prepend((string)__('Contact Us'));

        return $this->getResult();
    }
}
