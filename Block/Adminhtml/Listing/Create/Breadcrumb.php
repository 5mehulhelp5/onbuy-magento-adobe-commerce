<?php

namespace M2E\OnBuy\Block\Adminhtml\Listing\Create;

/**
 * Class \M2E\OnBuy\Block\Adminhtml\Listing\Create\Breadcrumb
 */
class Breadcrumb extends \M2E\OnBuy\Block\Adminhtml\Widget\Breadcrumb
{
    //########################################

    public function _construct()
    {
        parent::_construct();

        $this->setId('onBuyListingBreadcrumb');

        $this->setSteps(
            [
                [
                    'id' => 1,
                    'title' => __('Step 1'),
                    'description' => __('General Settings'),
                ],
                [
                    'id' => 2,
                    'title' => __('Step 2'),
                    'description' => __('Policies'),
                ],
            ]
        );
    }

    //########################################
}
