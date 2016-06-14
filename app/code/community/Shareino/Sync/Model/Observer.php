<?php
class Shareino_Sync_Model_Observer
{
	public function attr_update($observer) {
		$product = $observer->getEvent()->getProduct();
		$productId=$product->getData("entity_id");
		Mage::helper( "sync" )->sync($productId);

	}
	

}