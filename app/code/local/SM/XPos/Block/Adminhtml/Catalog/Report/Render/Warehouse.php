<?php
/**
 * Created by PhpStorm.
 * User: Smartor
 * Date: 11/4/14
 * Time: 4:54 PM
 */
class SM_XPos_Block_Adminhtml_Catalog_Report_Render_Warehouse extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row) {
        if(Mage::getStoreConfig('xpos/general/integrate_xmwh_enabled') == 1){
            $warehouseid = $row->getData('warehouse_id');
            if (!empty($warehouseid)) {
                $user = Mage::getModel('xwarehouse/warehouse')->load($warehouseid);
                return $user->getData('label');
            }
            else{
                return 'NO Warehouse';
            }
        }
        else{
            return 'NO Warehouse';
        }
    }

}