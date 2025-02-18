<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\ControlPanel\Inspection\Inspector;

use M2E\Core\Model\ControlPanel\Inspection\IssueFactory;
use Magento\Backend\Model\UrlInterface;

class ConfigsValidity implements \M2E\Core\Model\ControlPanel\Inspection\InspectorInterface
{
    private UrlInterface $urlBuilder;
    private IssueFactory $issueFactory;
    private \M2E\Core\Model\Config\Repository $configRepository;
    private \M2E\OnBuy\Model\Connector\Client\Single $serverClient;

    public function __construct(
        \M2E\Core\Model\Config\Repository $configRepository,
        \M2E\OnBuy\Model\Connector\Client\Single $serverClient,
        UrlInterface $urlBuilder,
        IssueFactory $issueFactory
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->serverClient = $serverClient;
        $this->issueFactory = $issueFactory;
        $this->configRepository = $configRepository;
    }

    public function process(): array
    {
        $issues = [];

        try {
            $responseData = $this->getDiff();
        } catch (\Exception $exception) {
            $issues[] = $this->issueFactory->create($exception->getMessage());

            return $issues;
        }

        if (
            !isset($responseData['configs_info']['config'])
            || !is_array($responseData['configs_info']['config'])
        ) {
            $issues[] = $this->issueFactory->create(
                strtr(
                    'No info for this channel_title version',
                    [
                        'channel_title' => \M2E\OnBuy\Helper\Module::getChannelTitle(),
                    ]
                )
            );

            return $issues;
        }

        $difference = $this->createDiff($responseData['configs_info']['config']);

        if (!empty($difference)) {
            $issues[] = $this->issueFactory->create(
                'Wrong configs structure validity',
                $this->renderMetadata($difference)
            );
        }

        return $issues;
    }

    private function getDiff(): array
    {
        $command = new \M2E\Core\Model\Server\Connector\System\ConfigsGetInfoCommand();
        /** @var \M2E\Core\Model\Connector\Response $response */
        $response = $this->serverClient->process($command);

        return $response->getResponseData();
    }

    private function createDiff(array $originalVersionData): array
    {
        $currentData = [];

        foreach ($this->configRepository->getAllByExtension(\M2E\OnBuy\Helper\Module::IDENTIFIER) as $config) {
            $key = $this->createConfigRecordIdentifier($config->getGroup(), $config->getKey());
            $currentData[$key] = [
                'group' => $config->getGroup(),
                'key' => $config->getKey(),
                'value' => $config->getValue(),
            ];
        }

        $differences = [];

        foreach ($originalVersionData as $originalVersionItem) {
            $configIdentifier = $this->createConfigRecordIdentifier(
                $originalVersionItem['group'],
                $originalVersionItem['key']
            );

            if (array_key_exists($configIdentifier, $currentData)) {
                continue;
            }

            $differences[] = [
                'item' => $originalVersionItem,
                'solution' => 'insert',
            ];
        }

        return $differences;
    }

    private function renderMetadata(array $differenceResult): string
    {
        $html = <<<HTML
<table style="width: 100%;">
    <tr>
        <th style="width: 200px">Group</th>
        <th style="width: 200px">Key</th>
        <th style="width: 150px">Value</th>
        <th style="width: 50px">Action</th>
    </tr>
HTML;

        foreach ($differenceResult as $index => $row) {
            $url = $this->urlBuilder->getUrl(
                '*/controlPanel_database/addTableRow',
                [
                    'table' => 'config',
                ]
            );

            $actionWord = 'Insert';
            $styles = '';
            $onclickAction = <<<JS
const elem = jQuery(this);

new jQuery.ajax({
    url: '{$url}',
    method: 'get',
    data: elem.parents('tr').find('form').serialize(),
    success: function(transport) {
        elem.parents('tr').remove();
    }
});
JS;
            $group = $row['item']['group'] === null ? 'null' : $row['item']['group'];
            $key = $row['item']['key'] === null ? 'null' : $row['item']['key'];
            $value = $row['item']['value'] === null ? 'null' : $row['item']['value'];

            $html .= <<<HTML
<tr>
    <td>{$row['item']['group']}</td>
    <td>{$row['item']['key']}</td>
    <td>
        <form style="margin-bottom: 0; display: block; height: 20px">
            <input type="text"   name="value_value" value="{$value}">
            <input type="checkbox" name="cells[]" value="group" style="display: none;" checked="checked">
            <input type="checkbox" name="cells[]" value="key" style="display: none;" checked="checked">
            <input type="checkbox" name="cells[]" value="value" style="display: none;" checked="checked">
            <input type="hidden" name="value_group" value="{$group}">
            <input type="hidden" name="value_key" value="{$key}">
        </form>
    </td>
    <td>
        <a id="insert_id_{$index}" style= "{$styles}"
           onclick="{$onclickAction}" href="javascript:void(0);">{$actionWord}</a>
    </td>
</tr>
HTML;
        }

        $html .= '</table>';

        return $html;
    }

    private function createConfigRecordIdentifier(string $group, string $key): string
    {
        return sprintf('%s#%s', $group, $key);
    }
}
