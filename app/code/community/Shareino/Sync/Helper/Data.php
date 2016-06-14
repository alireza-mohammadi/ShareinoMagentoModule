<?php

class Shareino_Sync_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getProductById($productId)
    {
        $product = Mage::getModel('catalog/product')->load($productId);

        return $this->getProductDetail($product);
    }

    public function getProductDetail($product)
    {

        $attrs = $product->getData();


        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
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
            "image" => $attrs["sku"],
            "images" => $attrs["sku"],
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
            "meta_description"
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

}