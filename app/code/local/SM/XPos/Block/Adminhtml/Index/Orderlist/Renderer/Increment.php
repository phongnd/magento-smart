<?php
class SM_XPos_Block_Adminhtml_Index_Orderlist_Renderer_Increment extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected function _getValue(Varien_Object $row)
    {
        $data = $row->getId();
        $order = Mage::getModel('sales/order')->load($data);
        $html = '<a href="javascript:void(0);" onclick="onViewOrder('.$row->getId().',\'\');">'.$order->getIncrementId().'</a>';
        return $html;
    }
}