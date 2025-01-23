<?php

declare(strict_types=1);

namespace M2E\OnBuy\Setup\InstallHandler;

trait HandlerTrait
{
    private \M2E\Core\Helper\Module\Database\Tables $tablesHelper;

    public function __construct(\M2E\Core\Helper\Module\Database\Tables $tablesHelper)
    {
        $this->tablesHelper = $tablesHelper;
    }
}
