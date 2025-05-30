<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Manual\Realtime;

use M2E\OnBuy\Model\Product\Action\Manual\Realtime\AbstractRealtime;

class StopAndRemoveAction extends AbstractRealtime
{
    private \M2E\OnBuy\Model\Product\RemoveHandler $removeHandler;

    public function __construct(
        \M2E\OnBuy\Model\Product\RemoveHandler $removeHandler,
        \M2E\OnBuy\Model\Product\Action\Dispatcher $actionDispatcher,
        \M2E\OnBuy\Model\Product\ActionCalculator $calculator,
        \M2E\OnBuy\Model\Listing\LogService $listingLogService
    ) {
        parent::__construct($actionDispatcher, $calculator, $listingLogService);
        $this->removeHandler = $removeHandler;
    }

    protected function getAction(): int
    {
        return \M2E\OnBuy\Model\Product::ACTION_DELETE;
    }

    protected function prepareOrFilterProducts(array $listingsProducts): array
    {
        $result = [];
        foreach ($listingsProducts as $listingProduct) {
            if ($listingProduct->isRemovableFromChannel()) {
                $result[] = $listingProduct;

                continue;
            }

            $this->removeHandler->process(
                $listingProduct,
                \M2E\Core\Helper\Data::INITIATOR_USER
            );
        }

        return $result;
    }

    protected function calculateAction(
        \M2E\OnBuy\Model\Product $product,
        \M2E\OnBuy\Model\Product\ActionCalculator $calculator
    ): \M2E\OnBuy\Model\Product\Action {
        return \M2E\OnBuy\Model\Product\Action::createStop($product);
    }

    protected function logAboutSkipAction(
        \M2E\OnBuy\Model\Product $product,
        \M2E\OnBuy\Model\Listing\LogService $logService
    ): void {
    }
}
