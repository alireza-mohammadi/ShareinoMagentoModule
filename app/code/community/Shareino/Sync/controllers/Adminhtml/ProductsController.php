<?php

class Shareino_Sync_Adminhtml_ProductsController extends Mage_Adminhtml_Controller_Action
{

    protected function setResponse($data)
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($data));
    }

    public function indexAction()
    {
        $id = $this->getRequest()->getParam('page');
        $this->loadLayout();
        $this->_setActiveMenu('sharein_tab/');
        Mage::register('products', $this->selectAllProducts($id));
        $this->_addBreadcrumb(Mage::helper('sync')->__('Form'), Mage::helper('sync')->__('Synchronization'));

        $this->renderLayout();
    }

    public function saveProductsAction()
    {
        $ids = $this->getRequest()->getParam('products');
        $saveId = $this->compareIds($ids);

        Mage::getConfig()->saveConfig('shareino/shareino_selected_products', json_encode($saveId), 'default', 0);
        $this->setResponse(['status' => true, 'message' => 'کالاهای انتخاب شده به شرینو  ارسال خواهند شد.']);
    }

    public function deleteProductAction()
    {
        $removeId = $this->getRequest()->getParam('product');

        $json = Mage::getStoreConfig('shareino/shareino_selected_products');
        $ids = json_decode($json, true);

        //diff for remove item
        $newIds = $this->array_pluck(array_diff($ids, array($removeId)));

        Mage::getConfig()->saveConfig('shareino/shareino_selected_products', json_encode($newIds), 'default', 0);
        $this->setResponse(['status' => true, 'message' => 'کالا دیگر به شرینو ارسال نمیشود.']);
    }

    protected function selectAllProducts($page)
    {
        $ids = Mage::getModel('catalog/product')->getCollection();
        $ids->setPage($page, 30);

        $ids->addAttributeToFilter('status', 1)
            ->addFieldToFilter(array(array('attribute' => 'visibility', 'neq' => '1')));

        $products = array();
        foreach ($ids as $id) {
            $products[] = Mage::helper('sync')->getProductById($id->getId());
        }

        return $products;
    }

    protected function compareIds($newIds)
    {
        $json = Mage::getStoreConfig('shareino/shareino_selected_products');
        $oldIds = json_decode($json, true);

        if (empty($oldIds)) {
            return $newIds;
        }

        $ids = array_diff($newIds, $oldIds);
        foreach ($ids as $id) {
            $oldIds[] = $id;
        }

        return $oldIds;
    }

    protected function array_pluck($array, $column_name)
    {
        if (function_exists('array_column')) {
            return array_column($array, $column_name);
        }

        return array_map(function($element) use($column_name) {
            return $element[$column_name];
        }, $array);
    }

}
