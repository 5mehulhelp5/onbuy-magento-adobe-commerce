<?php

namespace M2E\OnBuy\Plugin\Menu\Magento\Backend\Model\Menu;

use M2E\OnBuy\Helper\View;

class Item extends \M2E\OnBuy\Plugin\AbstractPlugin
{
    /** @var array */
    private $menuTitlesUsing = [];

    /** @var \M2E\OnBuy\Helper\View\OnBuy */
    protected $view;
    /** @var \M2E\OnBuy\Helper\Module\Wizard */
    private $wizardHelper;

    public function __construct(
        \M2E\OnBuy\Helper\Module\Wizard $wizardHelper,
        View\OnBuy $view
    ) {
        $this->wizardHelper = $wizardHelper;
        $this->view = $view;
    }

    /**
     * @param \Magento\Backend\Model\Menu\Item $interceptor
     * @param \Closure $callback
     *
     * @return string
     */
    public function aroundGetClickCallback($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('getClickCallback', $interceptor, $callback, $arguments);
    }

    protected function processGetClickCallback($interceptor, \Closure $callback, array $arguments)
    {
        $id = $interceptor->getId();
        $urls = $this->getUrls();

        if (isset($urls[$id])) {
            return $this->renderOnClickCallback($urls[$id]);
        }

        return $callback(...$arguments);
    }

    /**
     * Gives able to display titles in menu slider which differ from titles in menu panel
     *
     * @param \Magento\Backend\Model\Menu\Item $interceptor
     * @param \Closure $callback
     *
     * @return string
     */
    public function aroundGetTitle($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('getTitle', $interceptor, $callback, $arguments);
    }

    protected function processGetTitle($interceptor, \Closure $callback, array $arguments)
    {
        if (
            $interceptor->getId() == View\OnBuy::MENU_ROOT_NODE_NICK
            && !isset($this->menuTitlesUsing[View\OnBuy::MENU_ROOT_NODE_NICK])
        ) {
            $wizard = $this->wizardHelper->getActiveWizard(
                View\OnBuy::NICK
            );

            if ($wizard === null) {
                $this->menuTitlesUsing[View\OnBuy::MENU_ROOT_NODE_NICK] = true;

                return 'OnBuy';
            }
        }

        return $callback(...$arguments);
    }

    private function getUrls()
    {
        return [
            'M2E_OnBuy::onbuy_help_center_knowledge_base'
            => 'https://help.m2epro.com',
        ];
    }

    private function renderOnClickCallback($url)
    {
        return "window.open('$url', '_blank'); return false;";
    }
}
