<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Wizard\InstallationOnBuy;

class AccountCreate extends Installation
{
    private \M2E\OnBuy\Helper\Module\Exception $exceptionHelper;
    private \M2E\OnBuy\Model\Account\Create $accountCreate;
    private \M2E\Core\Model\LicenseService $licenseService;
    private \M2E\OnBuy\Helper\View\Configuration $configurationHelper;

    public function __construct(
        \M2E\OnBuy\Helper\Module\Exception $exceptionHelper,
        \M2E\OnBuy\Model\Account\Create $accountCreate,
        \M2E\OnBuy\Helper\View\Configuration $configurationHelper,
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

        $this->exceptionHelper = $exceptionHelper;
        $this->accountCreate = $accountCreate;
        $this->licenseService = $licenseService;
        $this->configurationHelper = $configurationHelper;
    }

    public function execute()
    {
        $consumerKey = $this->getRequest()->getPost('consumer_key');
        $secretKey = $this->getRequest()->getPost('secret_key');
        $title = $this->getRequest()->getPost('title');
        $sellerId = (int)$this->getRequest()->getPost('seller_id');

        if (
            empty($consumerKey)
            || empty($secretKey)
            || empty($title)
            || empty($sellerId)
        ) {
            $this->messageManager->addErrorMessage(__('Please complete all required fields before saving the configurations.'));

            return $this->_redirect('*/*/installation');
        }

        try {
            $this->accountCreate->create(
                $title,
                $sellerId,
                $consumerKey,
                $secretKey,
            );

            $this->setStep($this->getNextStep());
        } catch (\Throwable $throwable) {
            $this->exceptionHelper->process($throwable);
            if (
                !$this->licenseService->get()->getInfo()->getDomainIdentifier()->isValid()
                || !$this->licenseService->get()->getInfo()->getIpIdentifier()->isValid()
            ) {
                $error = __(
                    'The %channel_title access obtaining is currently unavailable.<br/>Reason: %error_message
</br>Go to the <a href="%url" target="_blank">License Page</a>.',
                    [
                        'error_message' => $throwable->getMessage(),
                        'url' => $this->configurationHelper->getLicenseUrl(['wizard' => 1]),
                        'channel_title' => \M2E\OnBuy\Helper\Module::getChannelTitle(),
                    ],
                );
            } else {
                $error = __(
                    'The %channel_title access obtaining is currently unavailable.<br/>Reason: %error_message',
                    [
                        'error_message' => $throwable->getMessage(),
                        'channel_title' => \M2E\OnBuy\Helper\Module::getChannelTitle(),
                    ]
                );
            }

            $this->setJsonContent(['message' => $error]);

            return $this->getResult();
        }

        $this->setJsonContent([
            'url' => $this->getUrl('*/*/installation'),
        ]);

        return $this->getResult();
    }
}
