<?php

/**
 * Created by Saeed Darvish.
 * Email : sd.saeed.darvish@gmail.com
 * mobile : 09179960554
 */
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
        $this->_removeButton("add");

    }

    public function getOperationAction($op)
    {

        return $this->getUrl("*/*/$op",array('id' => $this->getRequest()->getParam('id')));
    }
}