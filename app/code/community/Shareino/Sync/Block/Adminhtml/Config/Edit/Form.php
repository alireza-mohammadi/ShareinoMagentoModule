<?php

class Shareino_Sync_Block_Adminhtml_Config_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * Preparing form
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/updateConfig', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
            )
        );
        $fieldSet = $form->addFieldset('entity_form', array(
            'legend' => Mage::helper('sync')->__('Entity Information')
        ));
        $fieldSet->addField('status', 'text', array(
            'label' => Mage::helper('sync')->__('Shareino Api Token :'),
            'name' => 'shareino_api_token',
            'value' => Mage::getStoreConfig("shareino/SHAREINO_API_TOKEN"),
            'class' => 'required-entry',
            'required' => true
        ));

        $this->setForm($form);
        $this->setValues($form->getData());
        $form->setUseContainer(true);
        return parent::_prepareForm();
    }

}
