<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\HealthStatus;

class Index extends \M2E\OnBuy\Controller\Adminhtml\AbstractHealthStatus
{
    private \M2E\OnBuy\Model\HealthStatus\Manager $statusManager;
    private \M2E\OnBuy\Model\HealthStatus\CurrentStatus $currentStatus;

    public function __construct(
        \M2E\OnBuy\Model\HealthStatus\Manager $statusManager,
        \M2E\OnBuy\Model\HealthStatus\CurrentStatus $currentStatus
    ) {
        parent::__construct();
        $this->statusManager = $statusManager;
        $this->currentStatus = $currentStatus;
    }

    public function execute()
    {
        $activeTab = $this->getRequest()->getParam('active_tab', null);
        $activeTab === null && $activeTab = \M2E\OnBuy\Block\Adminhtml\HealthStatus\Tabs::TAB_ID_DASHBOARD;

        $resultSet = $this->statusManager->doCheck();

        $this->currentStatus->set($resultSet);

        /** @var \M2E\OnBuy\Block\Adminhtml\HealthStatus\Tabs $tabsBlock */
        $tabsBlock = $this->getLayout()->createBlock(
            \M2E\OnBuy\Block\Adminhtml\HealthStatus\Tabs::class,
            '',
            [
                'resultSet' => $resultSet,
                'data' => [
                    'active_tab' => $activeTab,
                ],
            ]
        );

        if ($this->isAjax()) {
            $this->setAjaxContent(
                $tabsBlock->getTabContent($tabsBlock->getActiveTabById($activeTab))
            );

            return $this->getResult();
        }

        $this->addLeft($tabsBlock);
        $this->addContent($this->getLayout()->createBlock(\M2E\OnBuy\Block\Adminhtml\HealthStatus::class));

        $this->setPageHelpLink('https://docs-m2.m2epro.com/docs/m2e-onbuy-help-center/');

        $this->getResult()->getConfig()->getTitle()->prepend(__('Help Center'));
        $this->getResult()->getConfig()->getTitle()->prepend(__('Health Status'));

        return $this->getResult();
    }
}
