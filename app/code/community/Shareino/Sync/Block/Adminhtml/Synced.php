<?php

class Shareino_Sync_Block_Adminhtml_Synced extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        // The blockGroup must match the first half of how we call the block, and controller matches the second half
        // ie. sync/adminhtml_organize
        $this->_blockGroup = 'sync';
        $this->_controller = 'adminhtml_Synced';
        $this->_headerText = $this->__('Synchronization');
        parent::__construct();
        $this->_removeButton('add');
    }

    public function _prepareLayout()
    {
//        $head = $this->getLayout()->getBlock('head');
//        $head->addJs('lib/jquery/jquery-1.10.2.min.js');
//        $head->addJs("shareino/sync.js");

        return parent::_prepareLayout();
    }

    public function getOperationAction($op)
    {

        return $this->getUrl("*/*/$op", array('id' => $this->getRequest()->getParam('id')));
    }

}
