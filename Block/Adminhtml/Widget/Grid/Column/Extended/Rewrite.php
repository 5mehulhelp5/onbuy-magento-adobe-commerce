<?php

namespace M2E\OnBuy\Block\Adminhtml\Widget\Grid\Column\Extended;

use Magento\Backend\Block\Widget;

class Rewrite extends \Magento\Backend\Block\Widget\Grid\Column\Extended
{
    private \M2E\OnBuy\Helper\Module\Exception $exceptionHelper;

    public function __construct(
        \M2E\OnBuy\Helper\Module\Exception $exceptionHelper,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->exceptionHelper = $exceptionHelper;
    }

    public function getRowField(\Magento\Framework\DataObject $row)
    {
        $renderedValue = $this->getRenderer()->render($row);
        if ($this->getHtmlDecorators()) {
            $renderedValue = $this->_applyDecorators($renderedValue, $this->getHtmlDecorators());
        }

        /*
         * if column has determined callback for framing call
         * it before give away rendered value
         *
         * callback_function($renderedValue, $row, $column, $isExport)
         * should return new version of rendered value
         */
        $frameCallback = $this->getFrameCallback();
        if (is_array($frameCallback)) {
            try {
                $this->validateFrameCallback($frameCallback);
                $renderedValue = call_user_func($frameCallback, $renderedValue, $row, $this, false);
            } catch (\Throwable $e) {
                $this->exceptionHelper->process($e);
                $msg = sprintf(
                    'An error occurred on calling %s callback. Message: %s',
                    isset($frameCallback[1]) ? $frameCallback[1] : '',
                    $e->getMessage()
                );

                $errorBlock = $this->getLayout()->createBlock(\Magento\Framework\View\Element\Messages::class);

                $errorBlock->addError($msg);

                return $errorBlock->toHtml();
            }
        }

        return $renderedValue;
    }

    private function validateFrameCallback(array $callback)
    {
        if (!is_object($callback[0]) || !$callback[0] instanceof Widget) {
            throw new \InvalidArgumentException(
                'Frame callback host must be instance of Magento\\Backend\\Block\\Widget'
            );
        }
    }
}
