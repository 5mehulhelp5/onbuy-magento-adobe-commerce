<?php

namespace M2E\OnBuy\Controller\Adminhtml\Policy;

use M2E\OnBuy\Controller\Adminhtml\AbstractTemplate;

class NewAction extends AbstractTemplate
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
        $resultForward = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD);
        $resultForward->forward('edit');

        return $resultForward;
    }
}
