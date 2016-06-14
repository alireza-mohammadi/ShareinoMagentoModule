<?php

class Shareino_Sync_Block_Adminhtml_Form_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'sync';
        $this->_controller = 'adminhtml_form';
        $this->_headerText = Mage::helper('sync')->__('Edit Form');

        $this->_addButton('save_and_continue', array(
            'label' => Mage::helper('adminhtml')->__('Sync All Products'),
            'onclick' => "setLocation('{$this->getUrl('*/*/syncAll')}')",
            'class' => 'save',
        ), -100);
    }
}