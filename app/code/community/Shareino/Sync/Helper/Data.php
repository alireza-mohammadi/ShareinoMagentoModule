<?php

class Shareino_Sync_Helper_Data extends Mage_Core_Helper_Abstract
{

    const SHAREINO_API_URL = 'https://shareino.ir/api/v1/public/';
    //const SHAREINO_API_URL = 'http://shareino.dev/api/v1/public/';
    const Version = '1.0.1';

    public function sendRequset($url, $body, $method)
    {
        // Get api token from server
        $SHAREINO_API_TOKEN = Mage::getStoreConfig('shareino/SHAREINO_API_TOKEN');
        if ($SHAREINO_API_TOKEN) {

            // Init curl
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            // SSL check
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

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
                'User-Agent: Magento_module_' . self::Version
                )
            );

            // Get result
            $result = curl_exec($curl);

            // Get Header Response header
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            switch ($httpcode) {
                case 200:
                    return json_decode($result, true);
                case 401:
                    return array('status' => false, 'message' => 'خطا! توکن وارد شده معتبر نمیباشد.');
                case 403:
                    return array('status' => false, 'message' => 'خطا! دسترسی  مجاز نمیباشد.');
                case 408:
                    return array('status' => false, 'message' => 'خطا! درخواست منقضی شد.');
                default:
                    return array('status' => false, 'message' => 'Error : ' . $httpcode);
            }
        }
        return array('status' => false, 'message' => 'ابتدا توکن را از سرور شرینو دریافت کنید');
    }

    public function getAllProductIds()
    {
        $collection = Mage::getModel('catalog/product')
            ->getCollection();

        $collection->addAttributeToFilter('status', 1);
        $collection->addFieldToFilter(array(array('attribute' => 'visibility', 'neq' => '1')));
        $collection->addAttributeToFilter('entity_id', array('nin' => $this->getConfSimpleProduct()));

        $_productCollection = $collection->load();

        $ids = array();
        foreach ($_productCollection as $product) {
            $ids[] = $product->getId();
        }
        return $ids;
    }

    public function getConfigurableProduct()
    {
        $collection = Mage::getModel('catalog/product')
            ->getCollection();
        $collection->addAttributeToFilter('type_id', 'configurable');
        $collection->addAttributeToFilter('status', 1);
        $collection->addFieldToFilter(array(array('attribute' => 'visibility', 'neq' => '1')));

        $_productCollection = $collection->load();

        $ids = array();
        foreach ($_productCollection as $product) {
            $ids[] = $product->getId();
        }
        return $ids;
    }

    public function getConfSimpleProduct()
    {
        $ids = [];
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');

        $query = 'SELECT child_id FROM ' . $resource->getTableName('catalog/product_relation')
            . ' WHERE parent_id in ( ' . implode(' ,', $this->getConfigurableProduct()) . ');';

        $results = $readConnection->fetchAll($query);

        foreach ($results as $product) {
            $ids[] = $product['child_id'];
        }

        return $ids;
    }

    public function getProductById($productId)
    {
        $product = Mage::getModel('catalog/product')
            ->load($productId);
        return $this->getProductDetail($product);
    }

    public function getProductDetail($product)
    {
        $variations = array();
        if ($product->isConfigurable()) {
            $configurableProduct = $product->getTypeInstance(true);
            $confAttrs = $configurableProduct->getConfigurableAttributesAsArray($product);
            $chileds = $configurableProduct->getChildrenIds($product->entity_id);

            foreach ($chileds[0] as $child) {
                $children = Mage::getModel('catalog/product')->load($child);
                if ($children) {
                    $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($children);
                    $var = array(
                        'code' => $child,
                        'quantity' => $stock->getQty() >= 0 ? $stock->getQty() : 0,
                        'sku' => $children->getData('sku'),
                        'price' => $children->getData('price')
                    );

                    $attribute = array();
                    foreach ($confAttrs as $conf) {
                        $value = $children->getResource()
                            ->getAttribute($conf['attribute_code'])
                            ->getSource()
                            ->getOptionText($children->getData($conf['attribute_code']));

                        $attribute[$conf['attribute_code']] = array(
                            'label' => $conf['frontend_label'],
                            'value' => $value
                        );
                    }

                    $var['variation'] = $attribute;
                    $variations[] = $var;
                }
            }
        }

        $stock = Mage::getModel('cataloginventory/stock_item')
            ->loadByProduct($product);

        $images = $product->getMediaGalleryImages();

        $productImages = array();
        foreach ($images->getItems() as $image) {
            if ($image['disabled']) {
                continue;
            }
            $productImages[] = $image['url'];
        }

        $productDetail = array(
            'name' => $product->getName(),
            'code' => $product->getId(),
            'sku' => $product->getSku(),
            'price' => $product->getPrice(),
            'active' => $product->getStatus(),
            'discount' => $this->getDiscounts($product),
            'quantity' => $stock->getQty() >= 0 ? $stock->getQty() : 0,
            'weight' => $product->getWeight() ? $product->getWeight() : 0,
            'original_url' => 'http://' . $_SERVER['SERVER_NAME'] . '/' . $product->getUrlPath(),
            'original_url_1' => 'http://' . $_SERVER['SERVER_NAME'] . '/' . $product->getProductUrl(),
            'original_url_2' => 'http://' . $_SERVER['SERVER_NAME'] . '/' . $product->getUrlInStore(),
            'brand_id' => "",
            'short_content' => $product->getShortDescription(),
            'long_content' => $product->getDescription(),
            'meta_keywords' => $product->getMetaKeyword(),
            'meta_description' => $product->getMetaDescription(),
            'image' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage(),
            'images' => $productImages,
            'tags' => "",
            'variants' => $variations,
            'categories' => $this->getCategoryId($product->getId()),
            'available_for_order' => 1,
            'out_of_stock' => 0,
            'attributes' => $this->getAttributesProduct($product)
        );

        return $productDetail;
    }

    protected function getCategoryId($id)
    {
        $product = Mage::getModel('catalog/product')
            ->load($id);

        $categories = $product->getCategoryIds();

        $listCategories = [];
        foreach ($categories as $category) {
            $_cat = Mage::getModel('catalog/category')
                ->load($category);

            $listCategories [] = $_cat->getId();
        }
        return $listCategories;
    }

    protected function getDiscounts($product)
    {
        $listDiscounts = array();
        //قیمت های ویژه
        if ($product->getSpecialPrice()) {
            $listDiscounts[] = array(
                'amount' => $product->getPrice() - $product->getSpecialPrice(),
                'start_date' => $product->getsPecialFromDate() ? $product->getsPecialFromDate() : '0000-00-00 00:00:00',
                'end_date' => $product->getSpecialToDate() ? $product->getSpecialToDate() : '0000-00-00 00:00:00',
                'quantity' => 1,
                'type' => 0
            );
        }
        //قیمت های تعدادی
        if ($product->getTierPrice()) {
            $tierPrices = $product->getTierPrice();
            foreach ($tierPrices as $tierPrice) {
                $listDiscounts[] = array(
                    'amount' => $product->getPrice() - $tierPrice['price'],
                    'start_date' => '0000-00-00 00:00:00',
                    'end_date' => '0000-00-00 00:00:00',
                    'quantity' => (int)$tierPrice['price_qty'],
                    'type' => 0
                );
            }
        }

        return $listDiscounts;
    }

    protected function getAttributesProduct($product)
    {
        $data = array();
        $attributes = $product->getAttributes();
        foreach ($attributes as $attribute) {
            if ($attribute->getIsVisibleOnFront()) {
                $data [] = array(
                    'label' => $attribute->getStoreLabel(),
                    'value' => $attribute->getFrontend()->getValue($product),
                );
            }
        }

        return $data;
    }

}
