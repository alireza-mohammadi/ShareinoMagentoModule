<?php

class Shareino_Sync_Model_Observer
{

    public function attr_update($observer)
    {
        $run = false;

        $getProduct = $observer->getEvent()->getProduct();
        $productId = $getProduct->getData('entity_id');

        $type = Mage::getStoreConfig('shareino/shareino_send_type');
        if ($type === 0) {
            $run = true;
        } else if ($type === 1) {
            $ids = $this->getCategoryId($productId);
            $run = $this->isExist($ids);
        } else if ($type === 2) {

        }

        if ($run) {
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
    }

    public function delete_product($observer)
    {
        $product = $observer->getEvent()->getProduct();
        $productId = $product->getData('entity_id');

        $body = array(
            'type' => 'selected',
            'code' => array($productId)
        );
        Mage::helper('sync')->sendRequset('products', json_encode($body), 'DELETE');
    }

    protected function isExist($ids)
    {
        $inside = json_decode(Mage::getStoreConfig('shareino/shareino_selected_categories'), ture);

        foreach ($ids as $id) {
            if (array_search($id, $inside) !== false) {
                return true;
            }
        }
        return false;
    }

    protected function getCategoryId($id)
    {
        $product = Mage::getModel('catalog/product')
            ->load($id);

        $categories = $product->getCategoryIds();

        $ids = [];
        foreach ($categories as $category) {
            $ids [] = Mage::getModel('catalog/category')
                ->load($category)
                ->getId();
        }
        return $ids;
    }

}
