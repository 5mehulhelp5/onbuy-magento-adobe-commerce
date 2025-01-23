<?php

namespace M2E\OnBuy\Block\Adminhtml\Log\Listing\Product\View;

use M2E\OnBuy\Block\Adminhtml\Log\Listing\Product\AbstractView;

class View extends AbstractView
{
    protected function _toHtml()
    {
        $message = (string)__('This Log contains information about the actions applied to ' .
            'M2E OnBuy Connect Listings and related Items.');
        $helpBlock = $this
            ->getLayout()
            ->createBlock(\M2E\OnBuy\Block\Adminhtml\HelpBlock::class)
            ->setData([
                'content' => $message,
            ]);

        return $helpBlock->toHtml() . parent::_toHtml();
    }
}
