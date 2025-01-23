<?php

namespace M2E\OnBuy\Controller\Adminhtml\Synchronization\Log;

class Index extends \M2E\OnBuy\Controller\Adminhtml\Synchronization\AbstractLog
{
    public function execute()
    {
        $this->addContent(
            $this->getLayout()->createBlock(\M2E\OnBuy\Block\Adminhtml\Synchronization\Log::class)
        );
        $this->getResultPage()->getConfig()->getTitle()->prepend(__('Synchronization Logs'));

        return $this->getResult();
    }
}
