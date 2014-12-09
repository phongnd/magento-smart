<?php
/**
 * Author: HieuNT
 * Email: hieunt@smartosc.com
 */

class SM_XPos_Block_Adminhtml_Catalog_Report_Render_Method extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row) {
        $method = $row->getData('method');
        $paymentTitle = Mage::getStoreConfig('payment/'.$method.'/title');
        return $paymentTitle;
    }
}