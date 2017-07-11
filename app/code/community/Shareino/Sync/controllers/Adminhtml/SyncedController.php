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
        Mage::register('countProduct', $this->countProducts());
        $this->renderLayout();
    }

    public function syncProductsAction()
    {
        $page = $this->getRequest()->getParam('pageNumber');
        $type = Mage::getStoreConfig('shareino/shareino_send_type');

        switch ($type) {
            case 0:
                $result = $this->typeAllProducts($page);
                break;
            case 1:
                $this->typeSelectedProducts();
                break;
            case 2:
                $this->typeSelectedCategories();
                break;
        }
        $this->setResponse($result);
    }

    public function syncCategoryAction()
    {
        $ids = Mage::getModel('catalog/category')
            ->getTreeModel()
            ->load()
            ->getCollection()
            ->getAllIds();

        $listCategory = array();
        if ($ids) {
            foreach ($ids as $id) {
                $subcategories = Mage::getModel('catalog/category')
                    ->load($id);

                if ($subcategories->is_active) {
                    $listCategory[] = [
                        'id' => $subcategories->getId(),
                        'parent_id' => $subcategories->getParentId(),
                        'name' => $subcategories->getName()
                    ];
                }
            }
        }

        $data = Mage::helper('sync')->sendRequset('categories/sync', json_encode($listCategory), 'POST');
        $this->setResponse($data);
    }

    protected function typeAllProducts($page)
    {
        $ids = Mage::getModel('catalog/product')->getCollection();
        $ids->setPage($page, 50);

        $ids->addAttributeToFilter('status', 1)
            ->addFieldToFilter(array(array('attribute' => 'visibility', 'neq' => '1')));

        $products = array();
        foreach ($ids as $id) {
            $products[] = Mage::helper('sync')->getProductById($id->getId());
        }

        $results = Mage::helper('sync')->sendRequset('products', json_encode($products), 'POST');
        $this->saveSync($results);
        return $results;
    }

    protected function typeSelectedProducts()
    {

    }

    protected function typeSelectedCategories()
    {

    }

    protected function saveSync($results)
    {
        foreach ($results as $result) {
            $shsync = Mage::getModel('sync/synced')
                ->load($result['code'], 'product_id');

            $data = array(
                'product_id' => $result['code'],
                'status' => $result['status'],
                'errors' => !empty($result['errors']) ? implode(', ', $result['errors']) : ''
            );

            if ($shsync->isObjectNew()) {
                $data ['date_add'] = date('Y-m-d H:i:s');
                $shsync->setData($data)
                    ->save();
            } else {
                $data ['date_upd'] = date('Y-m-d H:i:s');
                $shsync->addData($data)
                    ->setId($shsync['id_shareino_sync'])
                    ->save();
            }
        }
    }

    protected function countProducts()
    {
        $type = Mage::getStoreConfig('shareino/shareino_send_type');

        switch ($type) {
            case 0:
                $result = Mage::helper('sync')->getCount();
                break;
            case 1:
                $this->typeSelectedProducts();
                break;
            case 2:
                $this->typeSelectedCategories();
                break;
        }

        return $result;
    }

}
