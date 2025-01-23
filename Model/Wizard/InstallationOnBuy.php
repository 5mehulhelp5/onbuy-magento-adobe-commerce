<?php

namespace M2E\OnBuy\Model\Wizard;

use M2E\OnBuy\Model\Wizard;

class InstallationOnBuy extends Wizard
{
    /** @var string[] */
    protected $steps = [
        'registration',
        'account',
      //  'settings',
        'listingTutorial',
    ];

    /**
     * @return string
     */
    public function getNick()
    {
        return \M2E\OnBuy\Helper\View\OnBuy::WIZARD_INSTALLATION_NICK;
    }
}
