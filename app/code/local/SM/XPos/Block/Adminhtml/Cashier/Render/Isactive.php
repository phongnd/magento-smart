<?php
class SM_XPos_Block_Adminhtml_Cashier_Render_Isactive extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row){
        $status = $row->getIsActive();
        if($status == 1){
            return "Active";
        }else{
            return "Inactive";
        }
    }

}

?>