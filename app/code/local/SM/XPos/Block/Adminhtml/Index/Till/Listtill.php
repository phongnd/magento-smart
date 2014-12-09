<?php
class SM_Xpos_Block_Adminhtml_Index_Till_Listtill extends Mage_Core_Block_Template{
    public function __construct(){

    }

    public function loadTill(){
        if(Mage::getStoreConfig('xpos/general/integrate_xmwh_enabled')){
            $warehouse_id = Mage::getSingleton('admin/session')->getWarehouseId();
            $collection = Mage::getModel('xpos/till')->getCollection()
                ->addFieldToFilter('warehouse_id',$warehouse_id)
                ->addFieldToFilter('is_active',1);
        }else{
            $collection = Mage::getModel('xpos/till')->getCollection()
                ->addFieldToFilter('is_active',1);
        }

        return $collection;
    }

}