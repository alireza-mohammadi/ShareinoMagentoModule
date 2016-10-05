<?php
include_once "../controllers/Adminhtml/SyncedController.php";

class Shareino_Sync_Model_Observer
{
    public function attr_update($observer)
    {
        $product = $observer->getEvent()->getProduct();
        $productId = $product->getData("entity_id");

        $controller = Mage::getControllerInstance(
            'The_Controller_Class',
            Mage::app()->getRequest(),
            Mage::app()->getResponse());

        var_dump($controller->getAllProductIds());
        die;

//
    }


}