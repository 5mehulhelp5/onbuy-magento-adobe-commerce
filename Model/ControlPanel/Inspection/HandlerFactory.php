<?php

namespace M2E\OnBuy\Model\ControlPanel\Inspection;

use Magento\Framework\ObjectManagerInterface;

class HandlerFactory
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param \M2E\OnBuy\Model\ControlPanel\Inspection\Definition $definition
     *
     * @return \M2E\OnBuy\Model\ControlPanel\Inspection\InspectorInterface
     */
    public function create(\M2E\OnBuy\Model\ControlPanel\Inspection\Definition $definition)
    {
        return $this->objectManager->create($definition->getHandler());
    }
}
