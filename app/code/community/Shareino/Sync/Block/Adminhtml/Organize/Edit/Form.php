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
            'values' => $this->getLocalCategories(),
        ));

        $fieldSet->addField('weight_factor', 'select', array(
            'label' => Mage::helper('sync')->__('دسته بندی شیراینو'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'shareino_weight_factor',
            'values' => array('-1' => 'Please Select..', '0.0001' => 'گرم', '1' => 'کیلو گرم'),
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
}