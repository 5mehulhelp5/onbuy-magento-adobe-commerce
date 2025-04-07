<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Policy\Description;

use M2E\OnBuy\Model\Policy\AffectedListingsProducts\AffectedListingsProductsAbstract;

class AffectedListingsProducts extends AffectedListingsProductsAbstract
{
    public function getTemplateNick(): string
    {
        return \M2E\OnBuy\Model\Policy\Manager::TEMPLATE_DESCRIPTION;
    }
}
