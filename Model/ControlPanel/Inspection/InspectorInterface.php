<?php

namespace M2E\OnBuy\Model\ControlPanel\Inspection;

interface InspectorInterface
{
    /**
     * @return \M2E\OnBuy\Model\ControlPanel\Inspection\Issue[]
     */
    public function process();
}
