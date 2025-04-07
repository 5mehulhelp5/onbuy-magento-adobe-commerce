<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Policy\Description;

use M2E\OnBuy\Controller\Adminhtml\Policy\AbstractDescription;

class CheckMagentoProductId extends AbstractDescription
{
    public function execute()
    {
        $productId = $this->getRequest()->getParam('product_id', -1);

        $this->setJsonContent([
            'result' => $this->isMagentoProductExists($productId),
        ]);

        return $this->getResult();
    }
}
