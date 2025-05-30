<?php

namespace M2E\OnBuy\Block\Adminhtml\Magento\Renderer;

class JsPhpRenderer extends AbstractRenderer
{
    protected $jsPhp = [];

    public function addConstants(array $constants)
    {
        $this->jsPhp = array_merge($this->jsPhp, $constants);

        return $this;
    }

    public function render()
    {
        if (empty($this->jsPhp)) {
            return '';
        }

        $constants = \M2E\Core\Helper\Json::encode($this->jsPhp);

        return "OnBuy.php.add({$constants});";
    }
}
