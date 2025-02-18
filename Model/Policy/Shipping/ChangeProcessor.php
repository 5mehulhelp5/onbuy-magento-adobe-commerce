<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Policy\Shipping;

class ChangeProcessor extends \M2E\OnBuy\Model\Policy\ChangeProcessorAbstract
{
    public const INSTRUCTION_INITIATOR = 'template_shipping_change_processor';

    //########################################

    protected function getInstructionInitiator(): string
    {
        return self::INSTRUCTION_INITIATOR;
    }

    // ---------------------------------------

    /**
     * @param \M2E\OnBuy\Model\Policy\Shipping\Diff $diff
     */
    protected function getInstructionsData(
        \M2E\OnBuy\Model\ActiveRecord\Diff $diff,
        int $status
    ): array {
        $data = [];

        /** @var \M2E\OnBuy\Model\Policy\Shipping\Diff $diff */
        if ($diff->isShippingDataDifferent()) {
            $data[] = [
                'type' => self::INSTRUCTION_TYPE_SHIPPING_DATA_CHANGED,
                'priority' => 80,
            ];
        }

        return $data;
    }
}
