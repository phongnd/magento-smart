<?php
/**
 * Created by PhpStorm.
 * User: smartosc
 * Date: 11/28/14
 * Time: 9:50 AM
 */
class GHA_Customer_IndexController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
	{
		$this->loadLayout();
//		$customer = Mage::getSingleton('customer/session')->getCustomer();
//		$customer = Mage::getModel('customer/customer')->load($customerId);
//		$customer->setCustomerType('0');
//		var_dump($customer->getId());
//		var_dump($customer->getCustomerType());
		$this->renderLayout();
	}
	public function createSubCustomerAction(){
		$data = $this->getRequest()->getPost();
//		var_dump($data);
		$email = $this->getRequest()->getParam('email');
//		var_dump($email);
		$websiteId = Mage::app()->getWebsite()->getId();
		$store = Mage::app()->getStore();

		if(Mage::getSingleton('customer/session')->isLoggedIn()) {
			$customerData = Mage::getSingleton('customer/session')->getCustomer();
			$customerId = $customerData->getId();
			$subCustomer = Mage::getModel('customer/customer');
			$subCustomer ->setWebsiteId($websiteId)
			             ->setStore($store)
				         ->setFirstname($email)
				         ->setLastname("Subcustomer")
				         ->setEmail($email)
				         ->setPassword($subCustomer->generatePassword())
				         ->setCustomerType("subcustomer")
				         ->setManagerVendor($customerId);
			$subCustomer->save();
			$subCustomer->sendNewAccountEmail();
//			$subCustomer->sendPasswordReminderEmail();
		}
//		var_dump($subCustomer);


		$this->_redirect('customer/account');
	}
}