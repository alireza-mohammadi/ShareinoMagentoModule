<?php

class Shareino_Sync_Block_Adminhtml_Configured extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_blockGroup = 'sync';
        $this->_controller = 'adminhtml_Configured';
        $this->_headerText = $this->__('مدیریت دسته بندی ها و محصولات');
        parent::__construct();
        $this->_removeButton('add');
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getOperationAction($op)
    {

        return $this->getUrl("*/*/$op", array('id' => $this->getRequest()->getParam('id')));
    }

}
