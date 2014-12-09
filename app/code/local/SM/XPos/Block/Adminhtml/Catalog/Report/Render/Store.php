<?php
/**
 * Author: HieuNT
 * Email: hieunt@smartosc.com
 */

class SM_XPos_Block_Adminhtml_Catalog_Report_Render_Store extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row) {
        $storeId = $row->getData('store_id');

        $store = Mage::getModel('core/store')->load($storeId);
        return $store->getName ();
    }
}