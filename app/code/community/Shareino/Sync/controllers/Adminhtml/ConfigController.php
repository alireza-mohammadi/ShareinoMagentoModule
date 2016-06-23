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
                Mage::getSingleton('core/session')->addSuccess(Mage::helper("sync")->__("Api token updated"));
            } else {
                Mage::getSingleton('core/session')->addError(Mage::helper("sync")->__("Api token couldn't be null"));
            }
            $this->_redirect("*/*/");
        }
    }

    public function syncAllAction()
    {

        $collection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSelect('entity_id')
            ->addAttributeToFilter('status', array('eq' => 1))
            ->load();

        $products = array();
        foreach ($collection->getData() as $product) {
            $products[] = Mage::helper("sync")->getProductById($product["entity_id"]);
        }

        $result = Mage::helper("sync")->sendRequest("products", $products, "POST");

        $sync_failures = array();
        $sync_success = array();
        $failure = "Some Product couldn't sync with server : ";
        $success = "";
        $result = json_decode($result, true);
        if (is_array($result)) {
            foreach ($result as $sproducts) {
                if (!$sproducts["status"]) {
                    $sync_failures[] = $sproducts["code"];
                    $failure .= "( " . $sproducts["code"] . " : "
                        . $this->getErrors($sproducts["errors"]) . " ) |\t";

                } else
                    $sync_success[] = $sproducts["code"];

            }
        } else {
            if ($result["status"] == false) {
                $failure .= "\n Couldn't sync with shareino :"
                    . $this->getErrors($result["message"]);


            }
        }
        if (!empty($sync_success)) {
            Mage::getSingleton('core/session')->addSuccess(Mage::helper("sync")->__("Products synced with shareino Server"));
        }

        if (!empty($sync_failures))
            Mage::getSingleton('core/session')->addError(Mage::helper("sync")->__($failure));
        $this->_redirect("*/*/synchronize");
    }


    function getErrors($errors)
    {
        $msg = "";
        foreach ($errors as $error) {
            $msg .= "\n\t$error";
        }
        return $msg;
    }

    public function getAttributesAction()
    {
        echo "getAttributes";
    }

    public function deleteProductsAction()
    {
        if ($this->getRequest()->isPost()) {
            $body = array("type" => "all");
            $result = Mage::helper("sync")->sendRequest("products", $body, "DELETE");
            echo $result;
        }
    }

    public function syncCatAction()
    {
        $result = Mage::helper("sync")->sendRequest("categories", null, "GET");
        $result = json_decode($result, true);
        if ($result["status"]) {
            foreach ($result["categories"] as $category) {
                $this->addCategory($category, null);
            }
            Mage::getSingleton('core/session')->addSuccess(Mage::helper("sync")->__("All categories recived and synecd."));
        } else
            Mage::getSingleton('core/session')->addSuccess(Mage::helper("sync")->__("Couldn't recived and synced all categories."));
        $this->_redirect("*/*/synchronize");

    }

    function addCategory($newCategory, $parentId = null)
    {
        $category = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToFilter('url_key', $newCategory["slug"])
            ->getFirstItem();

        if (empty($category->getData())) {

            try {

                $name = $newCategory["name"];
                $slug = $newCategory["slug"];

                $storeId = 0;
                $category = Mage::getModel('catalog/category');
                $category->setName($name);
                $category->setUrlKey($slug);
                $category->setIsActive(1);
                $category->setDisplayMode('PRODUCTS');
                $category->setIsAnchor(1); //for active anchor
                $category->setStoreId($storeId);
                $parentId = $parentId != null ? $parentId : Mage_Catalog_Model_Category::TREE_ROOT_ID;
                $parentCategory = Mage::getModel('catalog/category')->load($parentId);
                $category->setPath($parentCategory->getPath());

                $category->save();
            } catch (Exception $e) {
                echo "Catch";

                print_r($e);
            }
        }

        $id = $category->getData("entity_id");
        foreach ($newCategory["children"] as $child) {
            $this->addCategory($child, $id);
        }
    }

}