<?php

class Shareino_Sync_Helper_Data extends Mage_Core_Helper_Abstract
{

    const SHAREINO_API_URL = "http://dev.scommerce.ir/api/v1/public/";
    //const SHAREINO_API_URL = "https://shareino.ir/api/v1/public/";
    const Version = "1.2.3";

    public function sendRequset($url, $body, $method)
    {
        // Get api token from server
        $SHAREINO_API_TOKEN = Mage::getStoreConfig("shareino/SHAREINO_API_TOKEN");
        if ($SHAREINO_API_TOKEN != null) {

            // Init curl
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            // SSL check
            //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            // Generate url and set method in url
            $url = self::SHAREINO_API_URL . $url;
            curl_setopt($curl, CURLOPT_URL, $url);

            // Set method in curl
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

            // Set Body if its exist
            if ($body != null) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
            }

            // Get result
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Authorization:Bearer $SHAREINO_API_TOKEN",
                "User-Agent: Magento_module_" . self::Version
                )
            );

            // Get result
            $result = curl_exec($curl);

            // Get Header Response header
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            if ($httpcode != 200) {
                $result = array();
                //if ($httpcode == 401 || $httpcode == 403) {
                Mage::getSingleton('core/session')->addError(Mage::helper("sync")->__("خطا ! لطفا صحت توکن و وضعیت دسترسی به وب سرویس شیرینو را بررسی کنید"));
                return;
                //}
            }
            return $result;
        }

        Mage::getSingleton('core/session')->addError(Mage::helper("sync")->__("توکن وارد نشده است"));
        return null;
        //return ("توکن وارد نشده است");
    }

}
