<?php

namespace M2E\OnBuy\Model\HealthStatus\Task;

abstract class InfoType extends AbstractModel
{
    public const TYPE = 'info';

    public function getType()
    {
        return self::TYPE;
    }
}
