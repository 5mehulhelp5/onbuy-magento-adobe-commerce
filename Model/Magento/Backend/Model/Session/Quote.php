<?php

namespace M2E\OnBuy\Model\Magento\Backend\Model\Session;

/**
 * Class \M2E\OnBuy\Model\Magento\Backend\Model\Session\Quote
 */
class Quote extends \Magento\Backend\Model\Session\Quote
{
    public function clearStorage()
    {
        parent::clearStorage();
        $this->_quote = null;

        return $this;
    }
}
