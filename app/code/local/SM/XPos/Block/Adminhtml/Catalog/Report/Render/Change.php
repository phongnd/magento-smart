<?php
/**
 * Author: HieuNT
 * Email: hieunt@smartosc.com
 */

class SM_XPos_Block_Adminhtml_Catalog_Report_Render_Change extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row) {
        $grand_total = $row->getData('grand_total');
        $total_paid = $row->getData('total_paid');
        return Mage::helper('core')->currency($total_paid-$grand_total, true, false);;
    }
}