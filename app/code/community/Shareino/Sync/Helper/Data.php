<?php

class Shareino_Sync_Helper_Data extends Mage_Core_Helper_Abstract
{
    const SHAREINO_API_URL = "http://dev.scommerce.ir/api/";

    /**
     * Called when need to send request to external server or site
     *
     * @param $url url address af Server
     * @param null $body content of request like product
     * @param $method
     * @return mixed | null
     */
    public function sendRequset($url, $body = null, $method)
    {

        // Init curl
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // Generate url and set method in url
        $url = self::SHAREINO_API_URL . $url;
        curl_setopt($curl, CURLOPT_URL, $url);

        // Set method in curl
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

        // Get token from site setting
        $SHAREINO_API_TOKEN = Mage::getStoreConfig("shareino/SHAREINO_API_TOKEN");


        // Check if token has been set then send request to {@link http://shareino.com}
        if (!empty($SHAREINO_API_TOKEN)) {

            // Set Body if its exist
            if ($body != null) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
            }
//            curl_setopt($curl, CURLOPT_HEADER, true);    // we want headers

            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    "Authorization:Bearer $SHAREINO_API_TOKEN")
            );

            // Get result
            $result = curl_exec($curl);;

            // Get Header Response header
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if ($httpcode != 200) {

                if ($httpcode == 401 || $httpcode == 403) {
                    Mage::getSingleton('core/session')->addError(Mage::helper("sync")
                        ->__("خطا ! لطفا صحت توکن و وضعیت دسترسی به وب سرویس شیرینو را بررسی کنید"));
                }
                return null;
            }
            return $result;
        } else {
            Mage::getSingleton('core/session')->addError(Mage::helper("sync")->__("توکن وارد نشده است"));
            return null;
        }
        return null;
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
        $SHAREINO_API_TOKEN = Mage::getStoreConfig("shareino/SHAREINO_API_TOKEN");


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