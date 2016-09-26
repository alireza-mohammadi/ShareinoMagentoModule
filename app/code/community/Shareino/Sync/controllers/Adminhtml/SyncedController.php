<?php
/**
 * Created by Saeed Darvish.
 * Email : sd.saeed.darvish@gmail.com
 * mobile : 09179960554
 */
class Shareino_Sync_Adminhtml_SyncedController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('sharein_tab/');
        $this->_addBreadcrumb(Mage::helper('sync')->__('Form'), Mage::helper('sync')->__('Synchronization'));
//        $this->_addContent($this->getLayout()->createBlock('sync/adminhtml_synced_edit'));
        $this->_addContent($this->getLayout()->createBlock('sync/adminhtml_synced'));

        $this->renderLayout();
    }

    public function  syncAllAction(){
        
    }
}