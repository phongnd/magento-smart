<?php
/**
 * Created by PhpStorm.
 * User: smartosc
 * Date: 11/28/14
 * Time: 10:36 AM
 */
class GHA_Customer_Block_Customer extends Mage_Core_Block_Template
{
	public function getCustomer()
	{
		return Mage::getSingleton('customer/session')->getCustomer();
	}
	public function getActionOfForm()
	{
		return $this->getUrl('gha/index/createSubCustomer');
	}
}