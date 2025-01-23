<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\HealthStatus\Notification\Magento\System;

use Magento\Framework\Notification\MessageInterface;
use M2E\OnBuy\Model\HealthStatus\Task\Result;

class Message implements MessageInterface
{
    private \M2E\OnBuy\Model\HealthStatus\CurrentStatus $currentStatus;
    private \M2E\OnBuy\Model\HealthStatus\Notification\Settings $notificationSettings;
    private \M2E\OnBuy\Model\HealthStatus\Notification\MessageBuilder $messageBuilder;
    private \M2E\OnBuy\Helper\Module\Maintenance $maintenanceHelper;

    public function __construct(
        \M2E\OnBuy\Model\HealthStatus\Notification\Settings $notificationSettings,
        \M2E\OnBuy\Model\HealthStatus\CurrentStatus $currentStatus,
        \M2E\OnBuy\Model\HealthStatus\Notification\MessageBuilder $messageBuilder,
        \M2E\OnBuy\Helper\Module\Maintenance $maintenanceHelper
    ) {
        $this->currentStatus = $currentStatus;
        $this->notificationSettings = $notificationSettings;
        $this->messageBuilder = $messageBuilder;
        $this->maintenanceHelper = $maintenanceHelper;
    }

    public function getIdentity(): string
    {
        if ($this->maintenanceHelper->isEnabled()) {
            return 'OnBuy-health-status-notification';
        }

        return sha1('OnBuy-health-status-' . $this->notificationSettings->getLevel());
    }

    public function isDisplayed(): bool
    {
        if ($this->maintenanceHelper->isEnabled()) {
            return false;
        }

        if (!$this->notificationSettings->isModeMagentoSystemNotification()) {
            return false;
        }

        if ($this->currentStatus->get() < $this->notificationSettings->getLevel()) {
            return false;
        }

        return true;
    }

    public function getText(): string
    {
        return $this->messageBuilder->build();
    }

    public function getSeverity(): int
    {
        switch ($this->currentStatus->get()) {
            case Result::STATE_NOTICE:
                return \Magento\Framework\Notification\MessageInterface::SEVERITY_NOTICE;

            case Result::STATE_WARNING:
                return \Magento\Framework\Notification\MessageInterface::SEVERITY_MAJOR;

            default:
            case Result::STATE_CRITICAL:
                return \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL;
        }
    }
}
