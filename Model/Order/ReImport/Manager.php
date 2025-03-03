<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order\ReImport;

class Manager
{
    private string $identifier;
    private \M2E\OnBuy\Model\Registry\Manager $registryManager;

    public function __construct(
        \M2E\OnBuy\Model\Account $account,
        \M2E\OnBuy\Model\Site $site,
        \M2E\OnBuy\Model\Registry\Manager $registryManager
    ) {
        $this->registryManager = $registryManager;
        $this->identifier = "{$account->getIdentifier()}_{$site->getSiteId()}";
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function isEnabled(): bool
    {
        return $this->getFromDate() !== null;
    }

    public function getFromDate(): ?\DateTimeImmutable
    {
        $date = $this->getSettings('from_date');
        if (empty($date)) {
            return null;
        }

        return \M2E\Core\Helper\Date::createImmutableDateGmt($date);
    }

    public function getToDate(): ?\DateTimeImmutable
    {
        $date = $this->getSettings('to_date');
        if (empty($date)) {
            return null;
        }

        return \M2E\Core\Helper\Date::createImmutableDateGmt($date);
    }

    public function setCurrentFromDate(\DateTimeInterface $currentFromDate): void
    {
        $this->setSettings('current_from_date', $currentFromDate->format('Y-m-d H:i:s'));
    }

    public function getCurrentFromDate(): ?\DateTimeImmutable
    {
        $date = $this->getSettings('current_from_date');
        if (empty($date)) {
            return null;
        }

        return \M2E\Core\Helper\Date::createImmutableDateGmt($date);
    }

    //----------------------------------------

    public function setFromToDates(\DateTimeInterface $fromDate, \DateTimeInterface $toDate): void
    {
        $this->validate($fromDate, $toDate);

        $this->setSettings('from_date', $fromDate->format('Y-m-d H:i:s'));
        $this->setSettings('to_date', $toDate->format('Y-m-d H:i:s'));
    }

    private function validate(\DateTimeInterface $from, \DateTimeInterface $to): void
    {
        if ($from->getTimestamp() > $to->getTimestamp()) {
            throw new \M2E\OnBuy\Model\Exception\Logic('From date is bigger than To date.');
        }

        $nowTimestamp = \M2E\Core\Helper\Date::createCurrentGmt()->getTimestamp();
        if (
            $from->getTimestamp() > $nowTimestamp
            || $to->getTimestamp() > $nowTimestamp
        ) {
            throw new \M2E\OnBuy\Model\Exception\Logic('Dates you provided are bigger than current.');
        }

        if ($from->diff($to)->days > 30) {
            throw new \M2E\OnBuy\Model\Exception\Logic('From - to interval provided is too big. (Max: 30 days)');
        }

        $minDate = \M2E\Core\Helper\Date::createCurrentGmt();
        $minDate->modify('-90 days');

        if ($from->getTimestamp() < $minDate->getTimestamp()) {
            throw new \M2E\OnBuy\Model\Exception\Logic('From date provided is too old. (Max: 90 days)');
        }
    }

    //----------------------------------------

    public function clear(): void
    {
        $this->deleteSettings();
    }

    // ----------------------------------------

    private function setSettings(string $key, string $value): void
    {
        $settings = $this->getSettings(null);
        $settings[$key] = $value;

        $this->registryManager->setValue($this->getRegistryKey(), json_encode($settings));
    }

    private function getSettings(?string $key)
    {
        $value = $this->registryManager->getValue($this->getRegistryKey());
        if ($value === null) {
            $value = [];
        } else {
            $value = (array)json_decode($value, true);
        }

        if ($key === null) {
            return $value;
        }

        return $value[$key] ?? null;
    }

    private function deleteSettings(): void
    {
        $this->registryManager->deleteValue($this->getRegistryKey());
    }

    private function getRegistryKey(): string
    {
        return "/orders/reimport/$this->identifier/";
    }
}
