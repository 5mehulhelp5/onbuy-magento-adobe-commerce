<?php

declare(strict_types=1);

namespace M2E\OnBuy\Block\Adminhtml\Category\Attributes\Validation;

class Popup extends \M2E\OnBuy\Block\Adminhtml\Magento\AbstractContainer
{
    protected $_template = 'M2E_OnBuy::category/attributes/validation_popup.phtml';

    public function getModalOpenUrl(): string
    {
        return $this->getUrl('*/category_attribute_validation_modal/open');
    }
}
