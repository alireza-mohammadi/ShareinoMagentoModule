<?php


class Shareino_Sync_Model_Observer
{
    public function attr_update($observer)
    {
        $product = $observer->getEvent()->getProduct();
        $productId = $product->getData("entity_id");

        $product = Mage::helper("sync")->getProductById($productId);

        $r = Mage::helper("sync")->sendRequset("products", json_encode($product), "POST");

        if ($r == null)
            return;
        $r = json_decode($r, true);
        foreach ($r as $item) {
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

    public function delete_product($observer)
    {
        $product = $observer->getEvent()->getProduct();
        $productId = $product->getData("entity_id");

        $url = "products";
        $body = array("type" => "selected", "code" => array($productId));
        $result = Mage::helper("sync")->sendRequset($url, json_encode($body), "DELETE");
    }

}