<?php

class Shareino_Sync_Adminhtml_SyncedController extends Mage_Adminhtml_Controller_Action
{

    protected function setResponse($data)
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($data));
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('sharein_tab/');
        $this->_addContent($this->getLayout()->createBlock('sync/adminhtml_synced'));
        $this->_addBreadcrumb(Mage::helper('sync')->__('Form'), Mage::helper('sync')->__('Synchronization'));
        Mage::register('productIDs', Mage::helper('sync')->getAllProductIds());
        $this->renderLayout();
    }

    public function syncProductsAction()
    {
        $ids = $this->getRequest()->getParam('ids');

        $products = array();
        foreach ($ids as $id) {
            $products[] = Mage::helper('sync')->getProductById($id);
        }
        $data = Mage::helper("sync")->sendRequset("products", json_encode($products), "POST");
        $this->setResponse($data);
    }

    public function syncCategoryAction()
    {
        $category = Mage::getModel('catalog/category');
        $tree = $category->getTreeModel();
        $tree->load();

        $ids = $tree->getCollection()->getAllIds();

        $listCategory = [];
        if ($ids) {
            foreach ($ids as $id) {
                $category->load($id);
                $subcategories = $category->getChildrenCategories();

                $parent_id = 0;
                foreach ($subcategories as $subcategory) {
                    $parent_id = $subcategory->getId();
                }

                $listCategory[] = [
                    'id' => $category->getId(),
                    'parent_id' => $parent_id,
                    'name' => $category->getName()
                ];
            }
        }

        $data = Mage::helper("sync")->sendRequset("categories/sync", json_encode($listCategory), "POST");
        $this->setResponse($data);
    }

}
