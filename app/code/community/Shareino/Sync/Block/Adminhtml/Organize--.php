<?php

class Shareino_Sync_Block_Adminhtml_Organize extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        // The blockGroup must match the first half of how we call the block, and controller matches the second half
        // ie. sync/adminhtml_organize
        $this->_blockGroup = 'sync';
        $this->_controller = 'adminhtml_organize';
        $this->_headerText = $this->__('Organize Categories');

        parent::__construct();
    }

}
