<?php
/**
 * Created by PhpStorm.
 * User: smartosc
 * Date: 11/28/14
 * Time: 10:09 AM
 */
class GHA_Customer_Model_Observer
{
	public function createVendor($observer){
		echo __METHOD__;
		$customer = $observer->getCustomer();
		$customer->setCustomerType('vendor');
	}
	public function addCreateSubcustomerLink($observer){
		$customerData = Mage::getSingleton("customer/session");
		$isLoggedIn = $customerData->isLoggedIn();
		$customer = $customerData->getCustomer();
		$customerType = $customer->getCustomerType();
		if ($isLoggedIn && $customerType == "vendor") {
			$observer->getEvent()->getLayout()->getUpdate()->addHandle('gha_create_customer_link');
		}
	}
}