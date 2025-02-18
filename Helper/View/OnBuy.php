<?php

namespace M2E\OnBuy\Helper\View;

class OnBuy
{
    public const NICK = 'onbuy';

    public const WIZARD_INSTALLATION_NICK = 'installationOnBuy';
    public const MENU_ROOT_NODE_NICK = 'M2E_OnBuy::onbuy';

    private \M2E\OnBuy\Helper\Module\Wizard $wizardHelper;

    public function __construct(
        \M2E\OnBuy\Helper\Module\Wizard $wizardHelper
    ) {
        $this->wizardHelper = $wizardHelper;
    }

    // ----------------------------------------

    public static function getWizardInstallationNick(): string
    {
        return self::WIZARD_INSTALLATION_NICK;
    }

    public function isInstallationWizardFinished(): bool
    {
        return $this->wizardHelper->isFinished(
            self::getWizardInstallationNick()
        );
    }
}
