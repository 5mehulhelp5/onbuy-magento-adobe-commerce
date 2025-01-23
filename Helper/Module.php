<?php

namespace M2E\OnBuy\Helper;

class Module
{
    public const IDENTIFIER = 'M2E_OnBuy';

    protected \M2E\OnBuy\Model\Registry\Manager $registry;
    protected \Magento\Backend\Model\UrlInterface $urlBuilder;
    private \M2E\Core\Helper\Module $coreModuleHelper;
    private \M2E\OnBuy\Model\Module $module;

    public function __construct(
        \M2E\OnBuy\Model\Module $module,
        \M2E\OnBuy\Model\Registry\Manager $registry,
        \Magento\Backend\Model\UrlInterface $urlBuilder,
        \M2E\Core\Helper\Module $coreModuleHelper
    ) {
        $this->registry = $registry;
        $this->urlBuilder = $urlBuilder;
        $this->coreModuleHelper = $coreModuleHelper;
        $this->module = $module;
    }

    // ----------------------------------------

    public function isDisabled(): bool
    {
        return $this->module->isDisabled();
    }

    public function isReadyToWork(): bool
    {
        return $this->module->isReadyToWork();
    }

    public function isStaticContentDeployed(): bool
    {
        return $this->coreModuleHelper->isStaticContentDeployed(self::IDENTIFIER);
    }

    public function getUpgradeMessages(): array
    {
        $messages = $this->registry->getValueFromJson('/upgrade/messages/');

        $messages = array_filter($messages, static function ($message) {
            return isset($message['text'], $message['type']);
        });

        foreach ($messages as &$message) {
            preg_match_all('/%[\w\d]+%/', $message['text'], $placeholders);
            $placeholders = array_unique($placeholders[0]);

            foreach ($placeholders as $placeholder) {
                $key = substr(substr($placeholder, 1), 0, -1);
                if (!isset($message[$key])) {
                    continue;
                }

                if (!strripos($placeholder, 'url')) {
                    $message['text'] = str_replace($placeholder, $message[$key], $message['text']);
                    continue;
                }

                $message[$key] = $this->urlBuilder->getUrl(
                    $message[$key],
                    isset($message[$key . '_args']) ? $message[$key . '_args'] : null
                );

                $message['text'] = str_replace($placeholder, $message[$key], $message['text']);
            }
        }
        unset($message);

        return $messages;
    }

    public function getBaseRelativeDirectory(): string
    {
        return $this->coreModuleHelper->getBaseRelativeDirectory(self::IDENTIFIER);
    }
}
