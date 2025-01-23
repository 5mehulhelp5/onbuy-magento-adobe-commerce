<?php

declare(strict_types=1);

namespace M2E\OnBuy\Block\Adminhtml\Site;

class Switcher extends \M2E\OnBuy\Block\Adminhtml\Switcher
{
    /** @var string */
    protected $paramName = 'site';
    private \M2E\OnBuy\Model\Site\Repository $siteRepository;

    public function __construct(
        \M2E\OnBuy\Model\Site\Repository $siteRepository,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->siteRepository = $siteRepository;
    }

    public function getLabel()
    {
        return (string)__('Site');
    }

    protected function loadItems()
    {
        $sites = $this->siteRepository->getAllGroupBySiteId();

        if (count($sites) === 1) {
            $this->hasDefaultOption = false;
            $this->setIsDisabled();
        }

        $items = [];
        foreach ($sites as $site) {
            $items['onbuy']['value'][] = [
                'value' => $site->getSiteId(),
                'label' => $site->getName(),
            ];
        }

        $this->items = $items;
    }

    private function setIsDisabled(): void
    {
        $this->setData('is_disabled', true);
    }
}
