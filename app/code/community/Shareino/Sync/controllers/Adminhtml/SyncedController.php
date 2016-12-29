<?php

/**
 * Created by Saeed Darvish.
 * Email : sd.saeed.darvish@gmail.com
 * mobile : 09179960554
 */
class Shareino_Sync_Adminhtml_SyncedController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('sharein_tab/');
        $this->_addBreadcrumb(Mage::helper('sync')->__('Form'), Mage::helper('sync')->__('Synchronization'));
        $this->_addContent($this->getLayout()->createBlock('sync/adminhtml_synced'));

        $this->renderLayout();
    }

    public function syncAllAction()
    {

        $ids = Mage::helper('sync')->getAllProductIds();

        $ids = array_chunk($ids, 75);
        $products = array();
        foreach ($ids as $key => $part) {
            foreach ($part as $id) {
                $products[$key][] = Mage::helper('sync')->getProductById($id);
            }
        }
        $results = array();
        foreach ($products as $part) {
            $r = Mage::helper("sync")->sendRequset("products", json_encode($part), "POST");
            if ($r == null)
                return;
            $results[] = json_decode($r, true);
        }

        foreach ($results as $part) {

            foreach ($part as $item) {
                $shsync = Mage::getModel("sync/synced");
                $data = array(
                    'product_id' => $item["code"],
                    'status' => $item["status"],
                    'errors' => isset($item["errors"]) & !empty($item["errors"]) ?
                        implode(", ", $item["errors"]) : "",
                    'updated_at' => date('Y-m-d H:i:s')
                );

                $shsync->setData($data);
                $shsync->save();
            }
        }
        Mage::getSingleton('core/session')->addError(Mage::helper("sync")
            ->__("همگام سازی تمام محصولات انحام شد "));

        $this->_redirect("*/*/");


    }


}