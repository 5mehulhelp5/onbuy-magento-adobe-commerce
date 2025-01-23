<?php

namespace M2E\OnBuy\Model\ActiveRecord;

abstract class AbstractModel extends \M2E\Core\Model\ActiveRecord\AbstractModel
{
    /**
     * @param string $fieldName
     *
     * @return array
     * @throws \M2E\OnBuy\Model\Exception\Logic
     */
    public function getSettings(string $fieldName): array
    {
        $settings = $this->getData($fieldName);

        if ($settings === null) {
            return [];
        }

        $settings = \M2E\Core\Helper\Json::decode($settings);

        return !empty($settings) ? $settings : [];
    }

    /**
     * @param string $fieldName
     * @param string|array $settingNamePath
     * @param mixed $defaultValue
     *
     * @return mixed|null
     */
    public function getSetting(
        $fieldName,
        $settingNamePath,
        $defaultValue = null
    ) {
        if (empty($settingNamePath)) {
            return $defaultValue;
        }

        $settings = $this->getSettings($fieldName);

        !is_array($settingNamePath) && $settingNamePath = [$settingNamePath];

        foreach ($settingNamePath as $pathPart) {
            if (!isset($settings[$pathPart])) {
                return $defaultValue;
            }

            $settings = $settings[$pathPart];
        }

        /** @psalm-suppress RedundantCondition */
        if (is_numeric($settings)) {
            $settings = ctype_digit((string)$settings) ? (int)$settings : $settings;
        }

        return $settings;
    }

    // ---------------------------------------

    /**
     * @param string $fieldName
     * @param array $settings
     *
     * @return \M2E\OnBuy\Model\ActiveRecord\AbstractModel
     * @throws \M2E\OnBuy\Model\Exception\Logic
     */
    public function setSettings($fieldName, array $settings = [])
    {
        $this->setData((string)$fieldName, \M2E\Core\Helper\Json::encode($settings));

        return $this;
    }

    /**
     * @param string $fieldName
     * @param string|array $settingNamePath
     * @param mixed $settingValue
     *
     * @return \M2E\OnBuy\Model\ActiveRecord\AbstractModel
     */
    public function setSetting(
        $fieldName,
        $settingNamePath,
        $settingValue
    ) {
        if (empty($settingNamePath)) {
            return $this;
        }

        $settings = $this->getSettings($fieldName);
        $target = &$settings;

        !is_array($settingNamePath) && $settingNamePath = [$settingNamePath];

        $currentPathNumber = 0;
        $totalPartsNumber = count($settingNamePath);

        foreach ($settingNamePath as $pathPart) {
            $currentPathNumber++;

            if (!array_key_exists($pathPart, $settings) && $currentPathNumber != $totalPartsNumber) {
                $target[$pathPart] = [];
            }

            if ($currentPathNumber != $totalPartsNumber) {
                $target = &$target[$pathPart];
                continue;
            }

            $target[$pathPart] = $settingValue;
        }

        $this->setSettings($fieldName, $settings);

        return $this;
    }
}
