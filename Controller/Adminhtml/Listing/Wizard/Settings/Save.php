<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Listing\Wizard\Settings;

class Save extends \M2E\OnBuy\Controller\Adminhtml\AbstractListing
{
    private \M2E\OnBuy\Model\Settings $settings;
    private \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory;

    public function __construct(
        \M2E\OnBuy\Model\Settings $settings,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    ) {
        parent::__construct();
        $this->settings = $settings;
        $this->jsonResultFactory = $jsonResultFactory;
    }

    public function execute()
    {
        $mode = (int) $this->getRequest()->getParam('identifier_code_mode');
        $attribute = (string) $this->getRequest()->getParam('identifier_code_custom_attribute');

        if (empty($attribute)) {
            return $this->jsonResultFactory->create()->setData(['result' => false]);
        }

        $this->settings->setIdentifierCodeMode($mode);
        $this->settings->setIdentifierCodeValue($attribute);

        return $this->jsonResultFactory->create()->setData(['result' => true]);
    }
}
