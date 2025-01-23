<?php

namespace M2E\OnBuy\Block\Adminhtml\Magento\Form\Element;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class \M2E\OnBuy\Block\Adminhtml\Magento\Form\Element\Separator
 */
class Separator extends AbstractElement
{
    protected function _construct()
    {
        parent::_construct();
        $this->setType('hidden');
    }

    public function getElementHtml()
    {
        $this->addClass('OnBuy-separator');

        return <<<HTML
<div id="{$this->getHtmlId()}" class="{$this->getClass()}">
    <hr/>
</div>
HTML;
    }
}
