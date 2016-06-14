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
        $helper = Mage::helper("sync");
        $product = $helper->getProductById(905);

        echo json_encode($product);
    }

    public function synchronizeAction()
    {
        echo "Shareino_Sync_Adminhtml_ConfigController_synchronizeAction";

    }

}