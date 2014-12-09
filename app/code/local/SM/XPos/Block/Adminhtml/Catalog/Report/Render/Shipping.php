<?php
/**
 * Author: HieuNT
 * Email: hieunt@smartosc.com
 */

class SM_XPos_Block_Adminhtml_Catalog_Report_Render_Shipping extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row) {
        $method = $row->getData('shipping_method');
        $method = explode('_',$method);
        $shippingTitle = Mage::getStoreConfig('carriers/'.$method[0].'/title');
        return $shippingTitle;
    }
}