<?php

namespace M2E\OnBuy\Model\Magento\Product;

class Index
{
    protected $indexerFactory;
    protected $indexers = [];

    /** @var \M2E\OnBuy\Model\Module\Configuration */
    private $moduleConfiguration;
    private \M2E\OnBuy\Model\Config\Manager $config;

    public function __construct(
        \M2E\OnBuy\Model\Config\Manager $config,
        \M2E\OnBuy\Model\Module\Configuration $moduleConfiguration,
        \Magento\Framework\Indexer\IndexerInterfaceFactory $indexerFactory
    ) {
        $this->indexerFactory = $indexerFactory;
        $this->moduleConfiguration = $moduleConfiguration;
        $this->config = $config;
    }

    /**
     * @return \Magento\Indexer\Model\Indexer
     */
    public function getIndexer($code)
    {
        if (isset($this->indexers[$code])) {
            return $this->indexers[$code];
        }

        return $this->indexers[$code] = $this->indexerFactory->create()->load($code);
    }

    /**
     * @return array
     */
    public function getIndexes()
    {
        return [
            'cataloginventory_stock',
        ];
    }

    //########################################

    public function disableReindex($code)
    {
        $indexer = $this->getIndexer($code);
        $mode = $indexer->getView()->getState()->getMode();

        if ($mode == \Magento\Framework\Mview\View\StateInterface::MODE_ENABLED) {
            return false;
        }

        //update by schedule
        $indexer->getView()
                ->getState()
                ->setMode(\Magento\Framework\Mview\View\StateInterface::MODE_ENABLED)
                ->save();

        return true;
    }

    public function enableReindex($code)
    {
        $indexer = $this->getIndexer($code);
        $mode = $indexer->getView()->getState()->getMode();

        if (!$mode) {
            return false;
        }

        if ($mode == \Magento\Framework\Mview\View\StateInterface::MODE_DISABLED) {
            return false;
        }

        $indexer->getView()
                ->getState()
                ->setMode(\Magento\Framework\Mview\View\StateInterface::MODE_DISABLED)
                ->save();

        return true;
    }

    // ---------------------------------------

    public function requireReindex($code)
    {
        return $this->getIndexer($code)->getStatus() === \Magento\Framework\Indexer\StateInterface::STATUS_INVALID;
    }

    public function executeReindex($code)
    {
        $indexer = $this->getIndexer($code);

        if ($indexer === false || $indexer->getStatus() == \Magento\Framework\Indexer\StateInterface::STATUS_WORKING) {
            return false;
        }

        $indexer->reindexAll();

        return true;
    }

    //########################################

    /**
     * @return bool
     */
    public function isIndexManagementEnabled()
    {
        return (bool)$this->moduleConfiguration->getProductIndexMode();
    }

    public function isDisabledIndex($code): bool
    {
        return (bool)(int)$this->config->get('/product/index/' . $code . '/', 'disabled');
    }

    // ---------------------------------------

    public function rememberDisabledIndex($code): void
    {
        $this->config->set('/product/index/' . $code . '/', 'disabled', 1);
    }

    public function forgetDisabledIndex($code)
    {
        $this->config->set('/product/index/' . $code . '/', 'disabled', 0);
    }

    // ---------------------------------------

    public function isEnabledIndex($code)
    {
        return (bool)(int)$this->config->get('/product/index/' . $code . '/', 'enabled');
    }

    public function rememberEnabledIndex($code)
    {
        $this->config->set('/product/index/' . $code . '/', 'enabled', 1);
    }

    public function forgetEnabledIndex($code)
    {
        $this->config->set('/product/index/' . $code . '/', 'enabled', 0);
    }
}
