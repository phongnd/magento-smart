<?php
/**
 * Created by PhpStorm.
 * User: Smartor
 * Date: 10/17/14
 * Time: 11:52 AM
 */

class SM_XPos_Block_Adminhtml_Report_Grid_Renderer_Cashier extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) {
        if($row->getData('cashier_id') == 0)
            return 'No Cashier';
        return $row->getData('firstname')." ".$row->getData('lastname');

    }
}