<?php

namespace M2E\OnBuy\Block\Adminhtml\ControlPanel\Tabs;

use M2E\OnBuy\Block\Adminhtml\Magento\Form\AbstractForm;

class Overview extends AbstractForm
{
    public function _construct()
    {
        parent::_construct();

        $this->setId('controlPanelOverview');
        $this->setTemplate('control_panel/tabs/overview.phtml');
    }

    protected function _beforeToHtml()
    {
        $this->setChild(
            'actual_info',
            $this->getLayout()->createBlock(\M2E\OnBuy\Block\Adminhtml\ControlPanel\Info\Actual::class)
        );

        $this->setChild(
            'license_info',
            $this->getLayout()->createBlock(\M2E\OnBuy\Block\Adminhtml\ControlPanel\Info\License::class)
        );

        //----------------------------------------

        $this->setChild(
            'cron_info',
            $this->getLayout()->createBlock(\M2E\OnBuy\Block\Adminhtml\ControlPanel\Inspection\Cron::class)
        );

        $this->setChild(
            'version_info',
            $this->getLayout()->createBlock(\M2E\OnBuy\Block\Adminhtml\ControlPanel\Inspection\VersionInfo::class)
        );

        //----------------------------------------

        $this->setChild(
            'database_general',
            $this->getLayout()->createBlock(
                \M2E\OnBuy\Block\Adminhtml\ControlPanel\Info\MysqlTables::class,
                '',
                [
                    'data' => [
                        'tables_list' => [
                            'Config' => [
                                \M2E\Core\Helper\Module\Database\Tables::TABLE_NAME_CONFIG,
                                \M2E\Core\Helper\Module\Database\Tables::TABLE_NAME_REGISTRY,
                            ],
                            'OnBuy' => [
                                \M2E\OnBuy\Helper\Module\Database\Tables::TABLE_NAME_ACCOUNT,
                                \M2E\OnBuy\Helper\Module\Database\Tables::TABLE_NAME_LISTING,
                                \M2E\OnBuy\Helper\Module\Database\Tables::TABLE_NAME_PRODUCT,
                                \M2E\OnBuy\Helper\Module\Database\Tables::TABLE_NAME_UNMANAGED_PRODUCT,
                            ],
                            'Processing' => [
                                \M2E\OnBuy\Helper\Module\Database\Tables::TABLE_NAME_PROCESSING,
                                \M2E\OnBuy\Helper\Module\Database\Tables::TABLE_NAME_PROCESSING_LOCK,
                            ],
                            'Additional' => [
                                \M2E\OnBuy\Helper\Module\Database\Tables::TABLE_NAME_LOCK_ITEM,
                                \M2E\OnBuy\Helper\Module\Database\Tables::TABLE_NAME_SYSTEM_LOG,
                                \M2E\OnBuy\Helper\Module\Database\Tables::TABLE_NAME_PRODUCT_INSTRUCTION,
                                \M2E\OnBuy\Helper\Module\Database\Tables::TABLE_NAME_PRODUCT_SCHEDULED_ACTION,
                                \M2E\OnBuy\Helper\Module\Database\Tables::TABLE_NAME_ORDER_CHANGE,
                                \M2E\OnBuy\Helper\Module\Database\Tables::TABLE_NAME_OPERATION_HISTORY,
                            ],
                        ],
                    ],
                ]
            )
        );

        return parent::_beforeToHtml();
    }
}
