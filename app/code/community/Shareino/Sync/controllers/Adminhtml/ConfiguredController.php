<?php

class Shareino_Sync_Adminhtml_ConfiguredController extends Mage_Adminhtml_Controller_Action
{

    protected function setResponse($data)
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($data));
    }

    public function indexAction()
    {
        $categories = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('level', 2)
            ->addAttributeToFilter('is_active', 1);

        Mage::register('categories', $this->makeTreeCategories($categories));
        Mage::register('token', Mage::getStoreConfig('shareino/shareino_api_token'));

        $this->loadLayout();
        $this->_setActiveMenu('sharein_tab/');

        //$this->_addContent($this->getLayout()->createBlock('sync/adminhtml_lists'));
        //$this->getLayout()->getBlock('head')->addJs('shareino/jquery.js');

        $this->_addBreadcrumb(Mage::helper('sync')->__('Form'), Mage::helper('sync')->__('Synchronization'));

        $this->renderLayout();
    }

    public function saveCategoryAction()
    {
        $ids = $this->getRequest()->getParam('categories');
        Mage::getConfig()->saveConfig('shareino/shareino_selected_categories', json_encode($ids), 'default', 0);
        $this->setResponse(['status' => true, 'message' => 'دسته بندی ارسال کالاها ذخیره شده.']);
    }

    public function saveTokenAction()
    {
        $token = $this->getRequest()->getParam('token');

        $message = 'توکن وارد شده صحیح نمیباشد.';
        $status = false;

        if (!empty($token)) {
            Mage::getConfig()->saveConfig('shareino/shareino_api_token', $token, 'default', 0);
            $message = 'توکن ذخیره شد.';
            $status = true;
        }

        $this->setResponse(['status' => $status, 'message' => $message]);
    }

    public function saveTypeAction()
    {
        $type = $this->getRequest()->getParam('type');

        Mage::getConfig()->saveConfig('shareino/shareino_send_type', $type, 'default', 0);
        $this->setResponse(['status' => true, 'message' => 'نوع روش ارسال ذخیره شد']);
    }

    protected function makeTreeCategories($categories)
    {
        $selected = Mage::getStoreConfig('shareino/shareino_selected_categories');
        $selectedId = json_decode($selected, ture);

        $out = '<ul>';
        foreach ($categories as $category) {

            $checked = in_array($category->getId(), $selectedId) ? 'checked' : '';

            $checkbox = "<input type=checkbox value=" . $category->getId() . " " . $checked . ">";

            $out .= "<li>$checkbox"
                . "<i class=collapse></i>"
                . "<span class=collapse>" . $category->getName() . "</span>";

            if ($category->hasChildren()) {
                $children = Mage::getModel('catalog/category')
                    ->getCategories($category->getId());
                $out .= $this->makeTreeCategories($children);
            }
            $out .= '</li>';
        }
        return $out . '</ul>';
    }

}
