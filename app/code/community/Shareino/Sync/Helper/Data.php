<?php

class Shareino_Sync_Helper_Data extends Mage_Core_Helper_Abstract
{
    private $SHAREINO_API_URL = "http://dev.scommerce.ir/api/";

    public function getAllProducts()
    {

        $ids = $this->getAllProductIds();
        $products = array();
        foreach ($ids as $id) {
            $products[] = $this->getProductById($id);
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

        $variations = array();

        if ($product->isConfigurable()) {

            $configurableProduct = $product->getTypeInstance(true);
            $confAttrs = $configurableProduct->getConfigurableAttributesAsArray($product);

            $chileds = $configurableProduct->getChildrenIds($product->entity_id);

            foreach ($chileds[0] as $child) {
                $children = Mage::getModel('catalog/product')->load($child);
//                $this->j($children->getData());
                if ($children) {
                    $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($children);
                    $var = array(
                        "code" => $child,
                        "quantity" => $stock->getQty() >= 0 ? $stock->getQty() : 0,
                        "sku" => $children->getData("sku"),
                        "price" => $children->getData("price")
                    );

                    $attribute = array();
                    foreach ($confAttrs as $conf) {
                        $value = $children->getResource()
                            ->getAttribute($conf["attribute_code"])
                            ->getSource()
                            ->getOptionText($children->getData($conf["attribute_code"]));;
                        $attribute[$conf["attribute_code"]] = array(
                            "label" => $conf["frontend_label"],
                            "value" => $value
                        );
                    }

                    $var["variation"] = $attribute;
                    $variations[] = $var;
                }

            }
        }


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
            "quantity" => $stock->getQty() >= 0 ? $stock->getQty() : 0,
            "weight" => $attrs["weight"],
            "url" => $product->getProductUrl(),
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
            "recurring_profile",
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
        $product_json["variants"] = $variations;

        $catsIDs = $product->getCategoryIds();
        $cats = array();
        foreach ($catsIDs as $category_id) {
            $_cat = Mage::getModel('catalog/category')->load($category_id);
            $cats[$_cat->getUrlKey()] = $_cat->getName();
        }
        $product_json["categories"] = $cats;

        return $product_json;
    }


    public function getAllProductsId()
    {

        /**
         * Get the resource model
         */
        $resource = Mage::getSingleton('core/resource');

        /**
         * Retrieve the read connection
         */
        $readConnection = $resource->getConnection('core_read');

        $productTable = $resource->getTableName('catalog/product');

        $relationTable = $resource->getTableName('catalog/product_relation');

        $query = 'SELECT * FROM ' . $resource->getTableName('catalog/product');

        /**
         * Execute the query and store the results in $results
         */
        $results = $readConnection->fetchAll($query);

        /**
         * Print out the results
         */
        var_dump($results);
    }

    public function getConfigurableProduct()
    {
        $ids = array();

        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addAttributeToFilter('type_id', 'configurable');
//        $collection->addAttributeToFilter('status', 1);
//        $collection->addFieldToFilter(array(array('attribute'=>'visibility', 'neq'=>"1" )));

        $_productCollection = $collection->load();

        foreach ($_productCollection as $product) {
            $ids[] = $product->getId();
        }
        return $ids;
    }

    public function getConfSimpleProduct()
    {

        $ids = [];
        /**
         * Get the resource model
         */
        $resource = Mage::getSingleton('core/resource');

        /**
         * Retrieve the read connection
         */
        $readConnection = $resource->getConnection('core_read');

        $query = 'SELECT child_id FROM ' . $resource->getTableName('catalog/product_relation')
            . ' WHERE parent_id in ( ' . implode(" ,", $this->getConfigurableProduct()) . ');';


        /**
         * Execute the query and store the results in $results
         */
        $results = $readConnection->fetchAll($query);

        foreach ($results as $product) {
            $ids[] = $product['child_id'];
        }

        return $ids;
    }

    public function getAllProductIds()
    {
        $ids = array();

        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addAttributeToFilter('status', 1);
        $collection->addFieldToFilter(array(array('attribute' => 'visibility', 'neq' => "1")));
        $collection->addAttributeToFilter('entity_id', array('nin' => $this->getConfSimpleProduct()));

        $_productCollection = $collection->load();

        foreach ($_productCollection as $product) {
            $ids[] = $product->getId();
        }
        return $ids;
    }

    public function sendProductToServer($products)
    {

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $url = $this->SHAREINO_API_URL . "products";
        curl_setopt($curl, CURLOPT_URL, $url);
        $products = json_encode($products);
        $SHAREINO_API_TOKEN = Mage::getStoreConfig("shareino/SHAREINO_API_TOKEN");
        if ($SHAREINO_API_TOKEN != null) {


            curl_setopt($curl, CURLOPT_POSTFIELDS, $products);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    "Accept : application/json",
                    "Authorization : Bearer $SHAREINO_API_TOKEN",
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

    }

    public function sync($id)
    {
        if ($id != null) {
            $this->sendProductToServer($this->getProductById($id));

        }
    }



    /**
     * Called when need to send request to external server or site
     *
     * @param $url url address af Server
     * @param null $body content of request like product
     * @param $method
     * @return mixed | null
     */
    public function sendRequest($url, $body = null, $method)
    {

        // Init curl
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // Generate url and set method in url
        $url = $this->SHAREINO_API_URL . $url;
        curl_setopt($curl, CURLOPT_URL, $url);

        // Set method in curl
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

        // Get token from site setting
        $SHAREINO_API_TOKEN =  Mage::getStoreConfig("shareino/SHAREINO_API_TOKEN");


        // Check if token has been set then send request to {@link http://shareino.com}
        if (!empty($SHAREINO_API_TOKEN)) {

            // Set Body if its exist
            if ($body != null) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
            }

            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    "Authorization:Bearer $SHAREINO_API_TOKEN")
            );

            return curl_exec($curl);
        }
        return null;
    }

    function dieObject($object, $kill = true, $json = false)
    {
        echo '<xmp style="text-align: left;">';
        if ($json)
            echo json_encode($object);
        else
            print_r($object);
        echo '</xmp><br />';

        if ($kill) {
            die('END');
        }

        return $object;
    }

    public function d($object, $json)
    {
        $this->dieObject($object, true, $json);

    }

    public function j($object)
    {
        echo json_encode($object);
        die;
    }

    public function p($object)
    {
        $this->dieObject($object, false);

    }

    public function v($object)
    {

        echo '<xmp style="text-align: left;">';
        var_dump($object);
        echo '</xmp><br />';
        return $object;
    }

}