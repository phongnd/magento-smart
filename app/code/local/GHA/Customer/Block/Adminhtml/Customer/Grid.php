<?php
/**
 * Created by PhpStorm.
 * User: smartosc
 * Date: 11/28/14
 * Time: 2:03 PM
 */
class GHA_Customer_Block_Adminhtml_Customer_Grid extends Mage_Adminhtml_Block_Customer_Grid
{
	public function setCollection($collection)
	{
//		$collection->addAttributeToFilter('customer_type', array('eq' => 'vendor'));
		parent::setCollection($collection);
	}
}