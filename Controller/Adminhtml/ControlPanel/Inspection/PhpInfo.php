<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\ControlPanel\Inspection;

class PhpInfo extends \M2E\OnBuy\Controller\Adminhtml\ControlPanel\AbstractMain
{
    public function execute()
    {
        phpinfo();
    }
}
