<?php

/**
 * Created by IntelliJ IDEA.
 * User: saeed
 * Date: 6/8/16
 * Time: 4:33 PM
 */
class Shareino_Sync_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction(){

            $this->loadLayout();
            $this->renderLayout();

    }
    public function testAction(){
        $this->loadLayout();
        $this->renderLayout();
    }

}