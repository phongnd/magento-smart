<?php
/**
 * Created by PhpStorm.
 * User: smartosc
 * Date: 12/8/14
 * Time: 11:02 AM
 */
class GHA_Customer_Block_Account_Navigation extends Mage_Customer_Block_Account_Navigation
{
	public function removeLinkByName($name){
		unset($this->_links[$name]);
		return $this;
	}
}