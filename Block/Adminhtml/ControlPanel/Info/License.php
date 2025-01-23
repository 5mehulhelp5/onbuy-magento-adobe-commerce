<?php

namespace M2E\OnBuy\Block\Adminhtml\ControlPanel\Info;

use M2E\OnBuy\Block\Adminhtml\Magento\AbstractBlock;

class License extends AbstractBlock
{
    private \M2E\Core\Helper\Client $clientHelper;
    private \M2E\OnBuy\Helper\Module $moduleHelper;
    public array $licenseData;
    /** @var array */
    public array $locationData;
    private \M2E\Core\Model\LicenseService $licenseService;

    public function __construct(
        \M2E\Core\Model\LicenseService $licenseService,
        \M2E\Core\Helper\Client $clientHelper,
        \M2E\OnBuy\Helper\Module $moduleHelper,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->clientHelper = $clientHelper;
        $this->moduleHelper = $moduleHelper;
        $this->licenseService = $licenseService;
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('controlPanelInfoLicense');
        $this->setTemplate('control_panel/info/license.phtml');
    }

    // ----------------------------------------

    protected function _beforeToHtml()
    {
        $license = $this->licenseService->get();

        $this->licenseData = [
            'key' => \M2E\Core\Helper\Data::escapeHtml($license->getKey()),
            'domain' => \M2E\Core\Helper\Data::escapeHtml($license->getInfo()->getDomainIdentifier()->getValidValue()),
            'ip' => \M2E\Core\Helper\Data::escapeHtml($license->getInfo()->getIpIdentifier()->getValidValue()),
            'valid' => [
                'domain' => $license->getInfo()->getDomainIdentifier()->isValid(),
                'ip' => $license->getInfo()->getIpIdentifier()->isValid(),
            ],
        ];

        $this->locationData = [
            'domain' => $this->clientHelper->getDomain(),
            'ip' => $this->clientHelper->getIp(),
            'directory' => $this->clientHelper->getBaseDirectory(),
            'relative_directory' => $this->moduleHelper->getBaseRelativeDirectory(),
        ];

        return parent::_beforeToHtml();
    }
}
