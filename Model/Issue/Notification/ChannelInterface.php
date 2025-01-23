<?php

namespace M2E\OnBuy\Model\Issue\Notification;

use M2E\OnBuy\Model\Issue\DataObject;

interface ChannelInterface
{
    /**
     * @param DataObject $message
     *
     * @return void
     */
    public function addMessage(DataObject $message): void;
}
