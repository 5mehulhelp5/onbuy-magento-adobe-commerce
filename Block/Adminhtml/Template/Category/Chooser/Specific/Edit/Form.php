<?php

namespace M2E\OnBuy\Block\Adminhtml\Template\Category\Chooser\Specific\Edit;

use M2E\OnBuy\Block\Adminhtml\Template\Category\Chooser\Specific\Form as AttributesForm;

class Form extends \M2E\OnBuy\Block\Adminhtml\Magento\Form\AbstractForm
{
    public function _construct()
    {
        parent::_construct();

        $this->setId('onBuyTemplateCategoryChooserSpecificEditForm');
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'edit_specifics_form',
                'action' => '',
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            ],
        ]);

        $formData = $this->getFormData();

        $fieldset = $form->addFieldset(
            'dictionary',
            [
                'legend' => __('Category Attributes'),
                'collapsable' => false,
            ]
        );

        if (!empty($formData['virtual_attributes'])) {
            $this->addAttributesTable(
                $fieldset,
                'virtual_attributes',
                $formData['virtual_attributes']
            );
        }

        if (!empty($formData['real_attributes'])) {
            $this->addAttributesTable(
                $fieldset,
                'real_attributes',
                $formData['real_attributes']
            );
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    private function getFormData()
    {
        return $this->getData('form_data');
    }

    private function addAttributesTable(
        \Magento\Framework\Data\Form\Element\Fieldset $fieldset,
        string $id,
        array $attributes
    ): void {
        /** @var AttributesForm\Renderer\Dictionary $renderer */
        $renderer = $this->getLayout()->createBlock(AttributesForm\Renderer\Dictionary::class);

        $config = [
            'specifics' => $attributes
        ];

        $field = $fieldset->addField($id, AttributesForm\Element\Dictionary::class, $config);
        $field->setRenderer($renderer);
    }
}
