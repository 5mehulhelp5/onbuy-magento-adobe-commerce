<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Order\UploadByUser;

class GetPopupHtml extends \M2E\OnBuy\Controller\Adminhtml\AbstractOrder
{
    public function execute()
    {
        /** @var \M2E\OnBuy\Block\Adminhtml\Order\UploadByUser\Popup $block */
        $block = $this->getLayout()->createBlock(\M2E\OnBuy\Block\Adminhtml\Order\UploadByUser\Popup::class);
        $this->setAjaxContent($block->toHtml());

        return $this->getResult();
    }
}
