<?php

namespace M2E\OnBuy\Controller\Adminhtml\Policy;

use M2E\OnBuy\Controller\Adminhtml\AbstractTemplate;

class NewTemplateHtml extends AbstractTemplate
{
    public function execute()
    {
        $nick = $this->getRequest()->getParam('nick');

        $this->setAjaxContent(
            $this->getLayout()->createBlock(
                \M2E\OnBuy\Block\Adminhtml\Listing\Template\NewTemplate\Form::class
            )
                 ->setData('nick', $nick)
        );

        return $this->getResult();
    }
}
