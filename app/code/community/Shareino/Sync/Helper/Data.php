<?php

class Shareino_Sync_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function sendRequset($url, $body, $method)
    {
        $apiToken = Mage::getStoreConfig('shareino/shareino_api_token');

        if ($apiToken) {
            $headers = array(
                "Authorization:Bearer $apiToken",
                "User-Agent:Magento_module_1.1.4"
            );

            $url = "https://dokme.com/api/v1/public/$url";
            $http = new Varien_Http_Adapter_Curl();

            $http->write($method, $url, '1.1', $headers, $body);
            $response = $http->read();

            $code = Zend_Http_Response::extractCode($response);
            switch ($code) {
                case 200:
                    return array('status' => true, 'message' => 'ارسال با موفقیت انجام شد.');
                case 401:
                    return array('status' => false, 'message' => 'خطا! توکن وارد شده معتبر نمیباشد.');
                case 403:
                    return array('status' => false, 'message' => 'خطا! دسترسی  مجاز نمیباشد.');
                case 408:
                    return array('status' => false, 'message' => 'خطا! درخواست منقضی شد.');
                case 429:
                case 0:
                    return array('status' => false, 'code' => 429, 'message' => 'فرایند ارسال محصولات به طول می انجامد لطفا صبور باشید.');
                default:
                    return array('status' => false, 'message' => $code);
            }
            $http->close();
        }

        return array('status' => false, 'message' => 'ابتدا توکن را از سرور شرینو دریافت کنید.');
    }

    public function getCount()
    {
        $count = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToFilter('status', 1)
            ->addFieldToFilter(array(array('attribute' => 'visibility', 'neq' => '1')))
            ->getSize();

        return $count;
    }

    public function getCountByCategory()
    {
        $selected = Mage::getStoreConfig('shareino/shareino_selected_categories');
        $Ids = json_decode($selected, ture);

        $count = 0;
        foreach ($Ids as $Id) {
            $count += Mage::getModel('catalog/category')
                ->load($Id)
                ->getProductCollection()
                ->getSize();
        }
        return $count;
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
            'original_url_1' => $product->getProductUrl(),
            'original_url_2' => $product->getUrlInStore(),
            'brand_id' => '',
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
            'out_of_stock' => $product->getStockItem()->getIsInStock() ? 1 : 0,
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
        if (($product->getPrice() != $product->getFinalPrice()) || $product->getSpecialPrice()) {
            $specialPrice = $product->getSpecialPrice() ? $product->getSpecialPrice() : $product->getFinalPrice();
            $listDiscounts[] = array(
                'amount' => $product->getPrice() - $specialPrice,
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
            if (!$attribute->getIsVisibleOnFront() || $product->getData($attribute->getName()) === null) {
                continue;
            }
            $data [] = array(
                'label' => $attribute->getStoreLabel(),
                'value' => $attribute->getFrontend()->getValue($product),
            );
        }

        return $data;
    }

}
