<?php

class Shareino_Sync_Model_Observer
{

    public function attr_update($observer)
    {
        $getProduct = $observer->getEvent()->getProduct();
        $productId = $getProduct->getData('entity_id');
        $product = Mage::helper('sync')->getProductById($productId);

        $results = Mage::helper('sync')->sendRequset('products', json_encode($product), 'POST');
        if ($results === null) {
            return;
        }

        foreach ($results as $result) {
            $shsync = Mage::getModel('sync/synced')
                ->load($result['code'], 'product_id');

            $data = array(
                'product_id' => $result['code'],
                'status' => $result['status'],
                'errors' => !empty($result['errors']) ? implode(', ', $result['errors']) : ''
            );

            if ($shsync->isObjectNew()) {
                $data ['date_add'] = date('Y-m-d H:i:s');
                $shsync->setData($data)
                    ->save();
            } else {
                $data ['date_upd'] = date('Y-m-d H:i:s');
                $shsync->addData($data)
                    ->setId($shsync['id_shareino_sync'])
                    ->save();
            }
        }
    }

    public function delete_product($observer)
    {
        $product = $observer->getEvent()->getProduct();
        $productId = $product->getData('entity_id');

        $url = 'products';
        $body = array('type' => 'selected', 'code' => array($productId));
        $result = Mage::helper('sync')->sendRequset($url, json_encode($body), 'DELETE');
    }

}
