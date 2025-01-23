<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Wizard\InstallationOnBuy;

class SettingsContinue extends Installation
{
    private \M2E\OnBuy\Model\Settings $settings;

    public function __construct(
        \M2E\OnBuy\Model\Settings $settings,
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
        $this->settings = $settings;
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if (empty($params)) {
            return $this->indexAction();
        }

        $this->settings->setConfigValues($params);

        $this->setStep($this->getNextStep());

        return $this->_redirect('*/*/installation');
    }
}
