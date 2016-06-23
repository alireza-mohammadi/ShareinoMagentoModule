<?php
class Shareino_Sync_Model_Observer
{
	public function attr_update($observer) {
		$product = $observer->getEvent()->getProduct();
		$productId=$product->getData("entity_id");
		$product=Mage::helper( "sync" )->getProductById($productId);
		$result = Mage::helper("sync")->sendRequest("products", $product, "POST");

		$sync_failures = array();
		$sync_success = array();
		$failure = "Some Product couldn't sync with server : ";
		$success = "";
		$result = json_decode($result, true);
		if (is_array($result)) {
			foreach ($result as $sproducts) {
				if (!$sproducts["status"]) {
					$sync_failures[] = $sproducts["code"];
					$failure .= "( " . $sproducts["code"] . " : "
						. $this->getErrors($sproducts["errors"]) . " ) |\t";

				} else
					$sync_success[] = $sproducts["code"];

			}
		} else {
			if ($result["status"] == false) {
				$failure .= "\n Couldn't sync with shareino :"
					. $this->getErrors($result["message"]);


			}
		}
		if (!empty($sync_success)) {
			Mage::getSingleton('core/session')->addSuccess(Mage::helper("sync")->__("Products synced with shareino Server"));
		}

		if (!empty($sync_failures))
			Mage::getSingleton('core/session')->addError(Mage::helper("sync")->__($failure));

	}
	

}