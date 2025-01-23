<?php

namespace M2E\OnBuy\Controller\Adminhtml\Policy;

use M2E\OnBuy\Controller\Adminhtml\AbstractTemplate;

class Index extends AbstractTemplate
{
    public function execute()
    {
        $content = $this->getLayout()->createBlock(\M2E\OnBuy\Block\Adminhtml\Template::class);

        $this->getResult()->getConfig()->getTitle()->prepend('Policies');
        $this->addContent($content);

        return $this->getResult();
    }
}
