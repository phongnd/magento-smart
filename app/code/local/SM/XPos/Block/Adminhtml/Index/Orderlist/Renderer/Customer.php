<?php
class SM_XPos_Block_Adminhtml_Index_Orderlist_Renderer_Customer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected function _getValue(Varien_Object $row)
    {
        $customer_firstname = $row->getData('customer_firstname');
        $customer_lastname = $row->getData('customer_lastname');
        return $customer_firstname . " " . $customer_lastname;
    }
}