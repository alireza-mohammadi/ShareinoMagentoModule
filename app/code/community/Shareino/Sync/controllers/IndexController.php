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

    public function testModelAction() {
        $params = $this->getRequest()->getParams();
        $blogpost = Mage::getModel('sync/synced');
        echo("Loading the blogpost with an ID of ".$params['id']);
        $blogpost->load($params['id']);
        $data = $blogpost->getData();
        var_dump($data);
    }

    public function showAllAction() {
        $posts = Mage::getModel('sync/organize')->getCollection();
        foreach($posts as $blogpost){
            echo '<h3>'.$blogpost->getNames().'</h3>';
            echo nl2br($blogpost->getIds());
        }
    }

}