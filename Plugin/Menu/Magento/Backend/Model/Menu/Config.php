<?php

namespace M2E\OnBuy\Plugin\Menu\Magento\Backend\Model\Menu;

use M2E\OnBuy\Helper\Module;
use M2E\OnBuy\Helper\View\OnBuy;
use M2E\OnBuy\Helper\Module\Maintenance;

class Config extends \M2E\OnBuy\Plugin\AbstractPlugin
{
    private const MENU_STATE_REGISTRY_KEY = '/menu/state/';
    private const MAINTENANCE_MENU_STATE_CACHE_KEY = 'maintenance_menu_state';

    private \Magento\Backend\Model\Menu\Item\Factory $itemFactory;
    private \M2E\OnBuy\Model\Registry\Manager $registry;

    protected bool $isProcessed = false;
    private \M2E\OnBuy\Helper\Data\Cache\Permanent $cache;
    private \M2E\Core\Helper\Magento $magentoHelper;
    /** @var \M2E\OnBuy\Helper\Module\Maintenance */
    private Maintenance $moduleMaintenanceHelper;
    /** @var \M2E\OnBuy\Helper\Module */
    private Module $moduleHelper;
    /** @var \M2E\OnBuy\Helper\Module\Wizard */
    private Module\Wizard $moduleWizardHelper;
    private \M2E\OnBuy\Model\Module $module;

    public function __construct(
        Module $moduleHelper,
        Module\Wizard $moduleWizardHelper,
        \M2E\OnBuy\Helper\Module\Maintenance $moduleMaintenanceHelper,
        \M2E\OnBuy\Model\Registry\Manager $registry,
        \M2E\OnBuy\Model\Module $module,
        \Magento\Backend\Model\Menu\Item\Factory $itemFactory,
        \M2E\OnBuy\Helper\Data\Cache\Permanent $cache,
        \M2E\Core\Helper\Magento $magentoHelper
    ) {
        $this->itemFactory = $itemFactory;
        $this->registry = $registry;
        $this->cache = $cache;
        $this->magentoHelper = $magentoHelper;
        $this->moduleMaintenanceHelper = $moduleMaintenanceHelper;
        $this->moduleHelper = $moduleHelper;
        $this->moduleWizardHelper = $moduleWizardHelper;
        $this->module = $module;
    }

    protected function canExecute(): bool
    {
        return $this->module->areImportantTablesExist();
    }

    public function aroundGetMenu(\Magento\Backend\Model\Menu\Config $interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('getMenu', $interceptor, $callback, $arguments);
    }

    protected function processGetMenu(
        \Magento\Backend\Model\Menu\Config $interceptor,
        \Closure $callback,
        array $arguments
    ) {
        /** @var \Magento\Backend\Model\Menu $menuModel */
        $menuModel = $callback(...$arguments);

        if ($this->isProcessed) {
            return $menuModel;
        }

        $this->isProcessed = true;

        // ---------------------------------------

        $maintenanceMenuState = $this->cache->getValue(
            self::MAINTENANCE_MENU_STATE_CACHE_KEY
        );

        if ($this->moduleMaintenanceHelper->isEnabled()) {
            if ($maintenanceMenuState === null) {
                $this->cache->setValue(
                    self::MAINTENANCE_MENU_STATE_CACHE_KEY,
                    true
                );
                $this->magentoHelper->clearMenuCache();
            }
            $this->processMaintenance($menuModel);

            return $menuModel;
        }

        if ($maintenanceMenuState !== null) {
            $this->cache->removeValue(
                self::MAINTENANCE_MENU_STATE_CACHE_KEY
            );
            $this->magentoHelper->clearMenuCache();
        }

        // ---------------------------------------

        $currentMenuState = $this->buildMenuStateData();
        $previousMenuState = $this->registry->getValueFromJson(self::MENU_STATE_REGISTRY_KEY);

        if ($previousMenuState != $currentMenuState) {
            $this->registry->setValue(self::MENU_STATE_REGISTRY_KEY, json_encode($currentMenuState));
            $this->magentoHelper->clearMenuCache();
        }

        // ---------------------------------------

        if ($this->moduleHelper->isDisabled()) {
            $this->processModuleDisable($menuModel);

            return $menuModel;
        }

        $this->processWizard($menuModel->get(OnBuy::MENU_ROOT_NODE_NICK));

        return $menuModel;
    }

    private function processMaintenance(\Magento\Backend\Model\Menu $menuModel)
    {
        $menuModelItem = $menuModel->get(OnBuy::MENU_ROOT_NODE_NICK);

        if ($menuModelItem !== null && $menuModelItem->isAllowed()) {
            $maintenanceMenuItemResource = OnBuy::MENU_ROOT_NODE_NICK;
        }

        foreach ($menuModel as $menuIndex => $menuItem) {
            if ($menuItem->getId() == $maintenanceMenuItemResource) {
                $maintenanceMenuItem = $this->itemFactory->create([
                    'id' => Maintenance::MENU_ROOT_NODE_NICK,
                    'module' => Module::IDENTIFIER,
                    'title' => 'OnBuy',
                    'resource' => $maintenanceMenuItemResource,
                    'action' => 'm2e_onbuy/maintenance',
                ]);

                $menuModel->remove($maintenanceMenuItemResource);
                $menuModel->add($maintenanceMenuItem, null, $menuIndex);
                break;
            }
        }

        $this->processModuleDisable($menuModel);
    }

    private function processModuleDisable(\Magento\Backend\Model\Menu $menuModel)
    {
        $menuModel->remove(OnBuy::MENU_ROOT_NODE_NICK);
    }

    private function processWizard(?\Magento\Backend\Model\Menu\Item $menu): void
    {
        if ($menu === null) {
            return;
        }

        $activeBlocker = $this->moduleWizardHelper->getActiveBlockerWizard(OnBuy::NICK);

        if ($activeBlocker === null) {
            return;
        }

        $menu->getChildren()->exchangeArray([]);

        $actionUrl = 'm2e_onbuy/wizard_' . $activeBlocker->getNick();
        $menu->setAction($actionUrl);
    }

    private function buildMenuStateData(): array
    {
        return [
            Module::IDENTIFIER => [
                $this->moduleHelper->isDisabled(),
            ],
            OnBuy::MENU_ROOT_NODE_NICK => [
                $this->moduleWizardHelper->getActiveBlockerWizard(OnBuy::NICK) === null,
            ],
        ];
    }
}
