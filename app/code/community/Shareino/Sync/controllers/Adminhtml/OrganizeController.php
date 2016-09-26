<?php

/**
 * Created by Saeed Darvish.
 * Email : sd.saeed.darvish@gmail.com
 * mobile : 09179960554
 */
class Shareino_Sync_Adminhtml_OrganizeController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function editAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($this->getRequest()->isPost()) {

            // Get Local Categorie Name and id
            $cat_id = $this->getRequest()->getParam('local_category');

            $category = Mage::getModel('catalog/category')->load($cat_id);
            $name = $category->getName();

            // Get shareino Categories name and id
            $ids = $this->getRequest()->getParam('shareino_category');

            $names = "";

            $cache = Mage::app()->getCache();
            $cache->load("shareino_categories");
            $categories = $cache->load("shareino_categories");

            if ($categories) {
                $categories = json_decode($categories, true);

                $names = $categories["categories"][$ids];
            }


            $data = array('cat_id' => $cat_id,
                'ids' => $ids,
                'name' => $name,
                'names' => $names,
                'model' => "category");

            // Check if on edit action or duplicat record
            $organizeModel = Mage::getModel('sync/organize');
            $id = $this->getRequest()->getParam("id");
            if (!$id) {

                if ($organizeModel->load($cat_id, "cat_id")->getData())
                    $id = $organizeModel->getId();
            }

            if ($id)
                $data["id_shareino_organized"] = $id;


            // The child category

            $organizeModel->setData($data);
            if ($organizeModel->save()) {
                Mage::getSingleton('core/session')
                    ->addSuccess(Mage::helper("sync")->__("معادل سازی با موفقیت ثبت شد"));
            } else
                Mage::getSingleton('core/session')->addError(Mage::helper("sync")->__("خطا لطفا مجددا تلاش فرمایید"));

            $this->_redirect("*/*/");
        }

    }

}