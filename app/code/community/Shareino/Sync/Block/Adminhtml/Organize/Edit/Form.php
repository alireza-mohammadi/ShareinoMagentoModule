<?php

class Shareino_Sync_Block_Adminhtml_Organize_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Preparing form
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */

    protected function _prepareForm()
    {
        $id = $this->getRequest()->getParam("id");
        $organizeModel = Mage::getModel('sync/organize')->load($id);

        $form = new Varien_Data_Form(array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            )
        );
        $fieldSet = $form->addFieldset('entity_form', array(
            'legend' => Mage::helper('sync')->__('Entity Information')
        ));


        $fieldSet->addField('local_category', 'select', array(
            'label' => Mage::helper('sync')->__('دسته بندی فروشگاه '),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'local_category',
            'value' => $organizeModel->getData("cat_id") ? $organizeModel->getData("cat_id") : null,
            'values' => $this->getLocalCategories(),
        ));

        $fieldSet->addField('shareino_category', 'select', array(
            'label' => Mage::helper('sync')->__('دسته بندی شیراینو'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'shareino_category',
            'value' => $organizeModel->getData("ids") ? $organizeModel->getData("ids") : null,
            'values' => $this->getShareinoCategories(),
        ));


        $this->setForm($form);
        $this->setValues($form->getData());
        $form->setUseContainer(true);
        return parent::_prepareForm();

    }

    public function getLocalCategories()
    {
        $collection = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('id')
            ->addAttributeToSelect('name')
            ->addIsActiveFilter();

        $collection = $collection->load();
        $categories = array();

        foreach ($collection as $category) {
            $categories[$category->getId()] = $category->getName();
        }

        return $categories;
    }

    public function getShareinoCategories()
    {
        $cache = Mage::app()->getCache();
        $cache->load("shareino_categories");
        $categories = $cache->load("shareino_categories");

        if (!$categories) {
            $categories = $this->helper("sync")->sendRequset("categories", null, "GET");
            $cache->save($categories, "shareino_categories", array("shareino_categories"), 1800);
        }

        $categories = json_decode($categories, true);
        $categories = $categories["categories"];
//        $categories=str_replace("--",'',$categories);
        return $categories;

    }
}