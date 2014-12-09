<?php
class SM_XPos_Block_Adminhtml_Till_Render_Warehouse extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row){
        if(Mage::getStoreConfig('xwarehouse/general/enabled')==1){
            $warehouse_name = $row->getWarehouseName();
            return $warehouse_name;
        }else{
            return "Default";
        }
    }

}

?>