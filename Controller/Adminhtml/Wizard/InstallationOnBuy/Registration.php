<?php

namespace M2E\OnBuy\Controller\Adminhtml\Wizard\InstallationOnBuy;

use M2E\OnBuy\Controller\Adminhtml\Context;

class Registration extends Installation
{
    private \M2E\Core\Model\RegistrationService $registrationService;

    public function __construct(
        \M2E\Core\Model\RegistrationService $registrationService,
        \M2E\Core\Helper\Magento $magentoHelper,
        \M2E\OnBuy\Helper\Module\Wizard $wizardHelper,
        \Magento\Framework\Code\NameBuilder $nameBuilder,
        \M2E\Core\Model\LicenseService $licenseService
    ) {
        parent::__construct(
            $magentoHelper,
            $wizardHelper,
            $nameBuilder,
            $licenseService,
        );
        $this->registrationService = $registrationService;
    }

    public function execute()
    {
        $this->init();

        return $this->registrationAction($this->registrationService);
    }
}
