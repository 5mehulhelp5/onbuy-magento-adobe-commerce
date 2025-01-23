<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Settings\License;

class Section extends \M2E\OnBuy\Controller\Adminhtml\AbstractBase
{
    public function execute()
    {
        $content = $this->getLayout()
                        ->createBlock(\M2E\OnBuy\Block\Adminhtml\System\Config\Sections\License::class);
        $this->setAjaxContent($content);

        return $this->getResult();
    }
}
