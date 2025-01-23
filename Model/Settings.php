<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model;

class Settings
{
    public const VALUE_MODE_NOT_SET = 0;
    public const VALUE_MODE_ATTRIBUTE = 1;

    public const CONFIG_GROUP = '/module/configuration/';
    public const CONFIG_KEY_IDENTIFIER_CODE_MODE = 'identifier_code_mode';
    public const CONFIG_KEY_IDENTIFIER_CODE_CUSTOM_ATTRIBUTE = 'identifier_code_custom_attribute';

    private \M2E\OnBuy\Model\Config\Manager $config;

    public function __construct(\M2E\OnBuy\Model\Config\Manager $config)
    {
        $this->config = $config;
    }

    //region Identifier

    public function isIdentifierCodeConfigured(): bool
    {
        return $this->getIdentifierCodeMode() === self::VALUE_MODE_ATTRIBUTE;
    }

    public function setIdentifierCodeMode(int $mode): void
    {
        $this->validateMode($mode);

        $this->config->set(self::CONFIG_GROUP, self::CONFIG_KEY_IDENTIFIER_CODE_MODE, $mode);
    }

    public function getIdentifierCodeMode(): int
    {
        return (int)$this->config->get(self::CONFIG_GROUP, self::CONFIG_KEY_IDENTIFIER_CODE_MODE);
    }

    public function setIdentifierCodeValue(string $value): void
    {
        $this->config->set(
            self::CONFIG_GROUP,
            self::CONFIG_KEY_IDENTIFIER_CODE_CUSTOM_ATTRIBUTE,
            $value
        );
    }

    public function getIdentifierCodeValue(): string
    {
        return (string)$this->config->get(self::CONFIG_GROUP, self::CONFIG_KEY_IDENTIFIER_CODE_CUSTOM_ATTRIBUTE);
    }

    //endregion

    public function setConfigValues(array $values): void
    {
        if (isset($values['identifier_code_mode'])) {
            $this->setIdentifierCodeMode((int)$values['identifier_code_mode']);
        }

        if (isset($values['identifier_code_custom_attribute'])) {
            $this->setIdentifierCodeValue((string)$values['identifier_code_custom_attribute']);
        }
    }

    // ----------------------------------------

    private function validateMode(int $mode): void
    {
        if (!in_array($mode, [self::VALUE_MODE_NOT_SET, self::VALUE_MODE_ATTRIBUTE])) {
            throw new \InvalidArgumentException('Invalid identifier code mode.');
        }
    }
}
