<?php

namespace M2E\OnBuy\Model\Magento\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Mutable
{
    private array $runtimeCache = [];
    private \Magento\Framework\App\Config\ScopeCodeResolver $scopeCodeResolver;
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    public function setValue(
        string $path,
        $value,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): self {
        $this->setToRuntimeCache(
            $this->preparePath($path, $scope, $scopeCode),
            $value,
        );

        return $this;
    }

    public function getValue(
        string $path,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        return $this->getFromRuntimeCache(
            $this->preparePath($path, $scope, $scopeCode)
        );
    }

    public function unsetValue(
        string $path,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): self {
        $this->removeFromRuntimeCache(
            $this->preparePath($path, $scope, $scopeCode)
        );

        return $this;
    }

    //----------------------------------------

    public function clear(): self
    {
        $this->clearRuntimeCache();

        return $this;
    }

    /*
     * Copied from \Magento\Framework\App\Config.php
     */
    private function preparePath($path, $scope, $scopeCode): string
    {
        if ($scope === 'store') {
            $scope = 'stores';
        } elseif ($scope === 'website') {
            $scope = 'websites';
        }

        $configPath = $scope;
        if ($scope !== 'default') {
            if (is_numeric($scopeCode) || $scopeCode === null) {
                $scopeCode = $this->getScopeCodeResolver()->resolve($scope, $scopeCode);
            } elseif ($scopeCode instanceof \Magento\Framework\App\ScopeInterface) {
                $scopeCode = $scopeCode->getCode();
            }
            if ($scopeCode) {
                $configPath .= '/' . $scopeCode;
            }
        }
        if ($path) {
            $configPath .= '/' . $path;
        }

        return $configPath;
    }

    private function getScopeCodeResolver()
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->scopeCodeResolver)) {
            $this->scopeCodeResolver = $this->objectManager->get(
                \Magento\Framework\App\Config\ScopeCodeResolver::class
            );
        }

        return $this->scopeCodeResolver;
    }

    // ----------------------------------------

    private function setToRuntimeCache(string $key, $value): void
    {
        $this->runtimeCache[$key] = $value;
    }

    private function getFromRuntimeCache(string $key)
    {
        return $this->runtimeCache[$key] ?? null;
    }

    private function removeFromRuntimeCache(string $key): void
    {
        unset($this->runtimeCache[$key]);
    }

    private function clearRuntimeCache(): void
    {
        $this->runtimeCache = [];
    }
}
