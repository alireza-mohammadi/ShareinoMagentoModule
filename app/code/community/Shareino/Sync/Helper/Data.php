<?php

class Shareino_Sync_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getAllProducts()
    {
        $collection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSelect('entity_id')
            ->addAttributeToFilter('status', array('eq' => 1))//only disabled
            ->load();
        $products = array();
        foreach ($collection->getData() as $product) {
            $products[] = $this->getProductById($product["entity_id"]);
        }
        return $products;
    }


    public function getProductById($productId)
    {
        $product = Mage::getModel('catalog/product')->load($productId);
        return $this->getProductDetail($product);
    }

    public function getProductDetail($product)
    {
        $attrs = $product->getData();

        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);

        $gallery_images = $product->getMediaGalleryImages();
        $galleris = array();
        foreach ($gallery_images->getItems() as $g_image) {
            $galleris[] = $g_image['url'];

        }
        $product_json = array(
            "name" => $attrs["name"],
            "code" => $attrs["entity_id"],
            "sku" => $attrs["sku"],
            "price" => $attrs["price"],
            "sale_price" => $attrs["sku"],
            "discount" => "",
            "quantity" => $stock->getQty(),
            "weight" => $attrs["weight"],
            "brand_id" => "",
            "categories" => "",
            "short_content" => $attrs["short_description"],
            "long_content" => $attrs["description"],
            "meta_keywords" => "",
            "meta_description" => "",
            "image" => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage(),
            "images" => $galleris,
            "attributes" => ""
        );

        $removeKeys = array(
            "entity_id",
            "entity_type_id",
            "attribute_set_id",
            "type_id",
            "sku",
            "has_options",
            "required_options",
            "created_at",
            "updated_at",
            "name",
            "url_key",
            "msrp_enabled",
            "msrp_display_actual_price_type",
            "meta_title",
            "meta_description",
            "image",
            "small_image",
            "thumbnail",
            "custom_design",
            "page_layout",
            "options_container",
            "gift_message_available",
            "url_path",
            "weight",
            "price",
            "special_price",
            "msrp",
            "status",
            "visibility",
            "tax_class_id",
            "is_recurring",
            "description",
            "short_description",
            "meta_keyword",
            "custom_layout_update",
            "news_from_date",
            "news_to_date",
            "special_from_date",
            "special_to_date",
            "custom_design_from",
            "custom_design_to",
            "group_price",
            "group_price_changed",
            "media_gallery",
            "tier_price",
            "tier_price_changed",
            "stock_item",
            "is_in_stock",
            "is_salable",
            "meta_keywords",
            "meta_description",
            "image_label",
            "thumbnail_label",
            "small_image_label"
        );

        foreach ($removeKeys as $key) {
            unset($attrs[$key]);
        }

        $customAttrs = array();
        foreach ($attrs as $key => $value) {
            $customAttrs[$key] = array(

                'label' => $product->getResource()->getAttribute($key)->getFrontend()->getLabel($product),
                'value' => $attrs[$key]
            );
        }
        $product_json["attributes"] = $customAttrs;


        $catsIDs = $product->getCategoryIds();
        $cats = array();
        foreach ($catsIDs as $category_id) {
            $_cat = Mage::getModel('catalog/category')->load($category_id);
            $cats[$_cat->getUrlKey()] = $_cat->getName();
        }
        $product_json["categories"] = $cats;

        return $product_json;
    }

    function sendProductToServer($products)
    {

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $url = 'http://shareino.ir/api/products';
        curl_setopt($curl, CURLOPT_URL, $url);
        $products = json_encode($products);
        $SHAREINO_API_TOKEN = Mage::getStoreConfig("shareino/apitoken");
        if ($SHAREINO_API_TOKEN != null) {


            curl_setopt($curl, CURLOPT_POSTFIELDS, $products);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    "Accept : application/json",
                    "Shareino-Api : $SHAREINO_API_TOKEN",
                    "Content-Type:application/json")
            );

            $result = curl_exec($curl);
        } else {
            Mage::getSingleton('core/session')->addError(Mage::helper("sync")->__("APi token does not exist"));
            return false;

        }
        $result = json_decode($result, true);
        if ($result['success']) {
            Mage::getSingleton('core/session')->addSuccess(Mage::helper("sync")->__("All Products sync with ShareINO server :-)"));
            return true;
        } else {
            $messages = $result['message'];
            $msg = "";
            foreach ($messages as $m)
                $msg .= "  " . $m;
            Mage::getSingleton('core/session')->addError(("Couldn't Sync product with ShareINO server :-(\n" . $msg));
            return false;
        }

    }

    public function syncAll()
    {
        $r = $this->sendProductToServer($this->getAllProducts());
        if ($r) {
            Mage::getConfig()->saveConfig('shareino/syncAll', "1", 'default', 0);
        }
    }

    public function sync($id)
    {
        if ($id != null) {
            $this->sendProductToServer($this->getProductById($id));

        }
    }

    /**
     * @param $url part of url to send request
     * @param null $body body of request
     * @param $method method of request
     * @return mixed|null return request's respones body or return null
     */
    public function sendRequset($url, $body = null, $method)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $url = SHAREINO_API_URL . $url;
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        $SHAREINO_API_TOKEN = get_site_option("SHAREINO_API_TOKEN");
        if (!empty($SHAREINO_API_TOKEN)) {

            if ($body != null)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    "Accept : application/json",
                    "Authorization : Bearer $SHAREINO_API_TOKEN",
                    "Content-Type:application/json")
            );

            return curl_exec($curl);
        }
        return null;
    }

}