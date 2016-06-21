<?php

/**
 * Created by IntelliJ IDEA.
 * User: saeed
 * Date: 6/8/16
 * Time: 4:19 PM
 */
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

    public function updateTokenAction()
    {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getParam("shareino_api_token");
            if ($token != null) {
                Mage::getConfig()->saveConfig('shareino/SHAREINO_API_TOKEN', $token, 'default', 0);
                $syncdAll = Mage::getStoreConfig("shareino/syncAll");
                Mage::getSingleton('core/session')->addSuccess(Mage::helper("sync")->__("Api token updated"));
                if ($syncdAll == null && $syncdAll != 1) {
                    Mage::helper("sync")->syncAll();
                }
            } else {
                Mage::getSingleton('core/session')->addError(Mage::helper("sync")->__("Api token couldn't be null"));
            }
            $this->_redirect("*/*/");
        }
    }

    public function syncAllAction()
    {
        Mage::helper("sync")->syncAll();
        $this->_redirect("*/*/");
    }


    public function getAttributesAction()
    {
        echo "getAttributes";
    }

    public function deleteProductsAction()
    {
        echo "deleteProducts";
    }

    public function syncCatAction()
    {

//        $result = Mage::helper("sync")->sendRequset("categories", null, "GET");

//        $result = json_decode($result, true);

        $this->addCategory("cat");
//        if ($result["status"]) {
//            foreach ($result["categories"] as $category) {
//
//                $this->addCategory($category, null);
//
//            }
//            return true;
//        } else
//            return false;

    }

    function addCategory($category, $parentId = null)
    {
        $category = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToFilter('url_key', $category["slug"])
            ->getFirstItem();

        var_dump($category->getData());
    }
}