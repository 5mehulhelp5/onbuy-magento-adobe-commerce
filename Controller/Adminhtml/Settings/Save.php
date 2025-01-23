<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Settings;

use M2E\OnBuy\Controller\Adminhtml\AbstractSettings;

class Save extends AbstractSettings
{
    private \M2E\OnBuy\Model\Settings $settings;

    public function __construct(
        \M2E\OnBuy\Model\Settings $settings
    ) {
        parent::__construct();

        $this->settings = $settings;
    }

    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        if (!$post) {
            $this->setJsonContent(['success' => false]);

            return $this->getResult();
        }

        if (isset($post['identifier_code_mode'])) {
            $this->settings->setIdentifierCodeMode((int)$post['identifier_code_mode']);
        }

        if (isset($post['identifier_code_custom_attribute'])) {
            $this->settings->setIdentifierCodeValue((string)$post['identifier_code_custom_attribute']);
        }

        $this->setJsonContent(['success' => true]);

        return $this->getResult();
    }
}
