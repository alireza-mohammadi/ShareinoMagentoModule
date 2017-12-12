<?php

class Shareino_Sync_IndexController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        //' SELECT * FROM `core_config_data` WHERE `path` LIKE '%shareino%''

        if (Mage::helper('core')->isModuleOutputEnabled('Shareino_Sync')) {
            echo json_encode(['status' => true], true);
        } else {
            echo json_encode(['status' => false], true);
        }
        //$this->loadLayout();
        //$this->renderLayout();
    }

}
