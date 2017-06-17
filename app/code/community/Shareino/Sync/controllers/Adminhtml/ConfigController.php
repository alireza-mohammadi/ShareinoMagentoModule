<?php

class Shareino_Sync_Adminhtml_ConfigController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('sharein_tab/');
        $this->_addBreadcrumb(Mage::helper('sync')->__('Form'), Mage::helper('sync')->__('Form'));

        $this->renderLayout();
    }

    public function synchronizeAction()
    {
        $this->loadLayout();
        $this->renderLayout();
        $this->_setActiveMenu('sharein_tab/');
        $this->_addBreadcrumb(Mage::helper('sync')->__('Form'), Mage::helper('sync')->__('Form'));
    }

    public function updateConfigAction()
    {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getParam('shareino_api_token');
            if ($token != null) {
                Mage::getConfig()->saveConfig('shareino/SHAREINO_API_TOKEN', $token, 'default', 0);
                Mage::getSingleton('core/session')->addSuccess(Mage::helper('sync')->__('توکن شما به روز رسانی شد.'));
            } else {
                Mage::getSingleton('core/session')->addError(Mage::helper('sync')->__('لطفا تمامی فیلد ها را تکمیل کنید'));
            }
            $this->_redirect('*/*/');
        }
    }

}
