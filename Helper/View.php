<?php

namespace M2E\OnBuy\Helper;

class View
{
    public const LISTING_CREATION_MODE_FULL = 0;
    public const LISTING_CREATION_MODE_LISTING_ONLY = 1;

    public const MOVING_LISTING_OTHER_SELECTED_SESSION_KEY = 'moving_listing_other_selected';
    public const MOVING_LISTING_PRODUCTS_SELECTED_SESSION_KEY = 'moving_listing_products_selected';

    private View\OnBuy $viewHelper;
    private View\OnBuy\Controller $controllerHelper;
    private \Magento\Framework\App\RequestInterface $request;

    public function __construct(
        \M2E\OnBuy\Helper\View\OnBuy $viewHelper,
        \M2E\OnBuy\Helper\View\OnBuy\Controller $controllerHelper,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->viewHelper = $viewHelper;
        $this->controllerHelper = $controllerHelper;
        $this->request = $request;
    }

    public function getViewHelper(): View\OnBuy
    {
        return $this->viewHelper;
    }

    public function getControllerHelper(): View\OnBuy\Controller
    {
        return $this->controllerHelper;
    }

    public function getCurrentView(): ?string
    {
        $controllerName = $this->request->getControllerName();
        if ($controllerName === null) {
            return null;
        }

        if (stripos($controllerName, \M2E\OnBuy\Helper\View\ControlPanel::NICK) !== false) {
            return \M2E\OnBuy\Helper\View\ControlPanel::NICK;
        }

        if (stripos($controllerName, 'system_config') !== false) {
            return \M2E\OnBuy\Helper\View\Configuration::NICK;
        }

        return \M2E\OnBuy\Helper\View\OnBuy::NICK;
    }

    // ---------------------------------------

    public function getModifiedLogMessage($logMessage)
    {
        return \M2E\Core\Helper\Data::escapeHtml(
            \M2E\OnBuy\Helper\Module\Log::decodeDescription($logMessage),
            ['a'],
            ENT_NOQUOTES
        );
    }
}
