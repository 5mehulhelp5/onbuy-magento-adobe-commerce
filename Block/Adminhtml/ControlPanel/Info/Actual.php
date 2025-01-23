<?php

namespace M2E\OnBuy\Block\Adminhtml\ControlPanel\Info;

use M2E\OnBuy\Block\Adminhtml\Magento\AbstractBlock;

class Actual extends AbstractBlock
{
    private \M2E\Core\Helper\Client $clientHelper;
    private \M2E\Core\Helper\Magento $magentoHelper;
    private \M2E\OnBuy\Model\Module\Environment $moduleEnv;
    private \M2E\OnBuy\Helper\Module\Maintenance $maintenanceHelper;

    /** @var string */
    public $systemName;
    /** @var int|string */
    public $systemTime;
    /** @var string */
    public $magentoInfo;
    /** @var string */
    public $publicVersion;
    /** @var mixed */
    public $setupVersion;
    /** @var mixed|null */
    public $moduleEnvironment;
    /** @var bool */
    public $maintenanceMode;
    /** @var false|mixed|string */
    public $coreResourceVersion;
    /** @var false|mixed|string */
    public $coreResourceDataVersion;
    /** @var array|string */
    public $phpVersion;
    /** @var string */
    public string $phpApi;
    /** @var float|int */
    public $memoryLimit;
    /** @var false|string */
    public $maxExecutionTime;
    public ?string $mySqlVersion;
    public string $mySqlDatabaseName;
    public string $mySqlPrefix;
    private \M2E\OnBuy\Model\Module $module;
    private \M2E\Core\Helper\Client\MemoryLimit $memoryLimitHelper;

    public function __construct(
        \M2E\Core\Helper\Client $clientHelper,
        \M2E\Core\Helper\Magento $magentoHelper,
        \M2E\OnBuy\Model\Module\Environment $moduleEnv,
        \M2E\Core\Helper\Client\MemoryLimit $memoryLimit,
        \M2E\OnBuy\Helper\Module\Maintenance $maintenanceHelper,
        \M2E\OnBuy\Model\Module $module,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->clientHelper = $clientHelper;
        $this->magentoHelper = $magentoHelper;
        $this->maintenanceHelper = $maintenanceHelper;
        $this->memoryLimitHelper = $memoryLimit;
        $this->module = $module;
        $this->moduleEnv = $moduleEnv;
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('controlPanelSummaryInfo');
        $this->setTemplate('control_panel/info/actual.phtml');
    }

    // ----------------------------------------

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $this->systemName = \M2E\Core\Helper\Client::getSystem();
        $this->systemTime = \M2E\Core\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s');
        // ---------------------------------------

        $this->magentoInfo = __(ucwords($this->magentoHelper->getEditionName())) .
            ' (' . $this->magentoHelper->getVersion() . ')';

        // ---------------------------------------
        $this->publicVersion = $this->module->getPublicVersion();
        $this->setupVersion = $this->module->getSetupVersion();
        $this->moduleEnvironment = $this->moduleEnv->isProductionEnvironment() ? 'production' : 'development';
        // ---------------------------------------

        // ---------------------------------------
        $this->maintenanceMode = $this->maintenanceHelper->isEnabled();
        $this->coreResourceVersion = $this->module->getSchemaVersion();
        $this->coreResourceDataVersion = $this->module->getDataVersion();
        // ---------------------------------------

        // ---------------------------------------
        $this->phpVersion = \M2E\Core\Helper\Client::getPhpVersion();
        $this->phpApi = \M2E\Core\Helper\Client::getPhpApiName();
        // ---------------------------------------

        // ---------------------------------------
        $this->memoryLimit = $this->memoryLimitHelper->get();
        $this->maxExecutionTime = ini_get('max_execution_time');
        // ---------------------------------------

        // ---------------------------------------
        $this->mySqlVersion = $this->clientHelper->getMysqlVersion();
        $this->mySqlDatabaseName = $this->magentoHelper->getDatabaseName();
        $this->mySqlPrefix = $this->magentoHelper->getDatabaseTablesPrefix();
        if (empty($this->mySqlPrefix)) {
            $this->mySqlPrefix = __('disabled');
        }

        // ---------------------------------------

        return parent::_beforeToHtml();
    }
}
