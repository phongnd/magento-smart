<?php
class SM_Xpos_Block_Adminhtml_Index_Warehouse_Listwarehouse extends Mage_Core_Block_Template{
    public function __construct(){

    }

    public function loadWarehouse(){
        $currentUserId = Mage::getSingleton('admin/session')->getUser()->getId();
        $allowWarehouse = Mage::getModel('xwarehouse/user')->getCollection();
        $allowWarehouse->getSelect()->join(Mage::getConfig()->getTablePrefix().'sm_warehouses', 'main_table.warehouse_id ='.Mage::getConfig()->getTablePrefix().'sm_warehouses.warehouse_id',array('label'));
        $allowWarehouse->addFieldToFilter('user_id',array('eq' => $currentUserId));
        return $allowWarehouse;
    }

}