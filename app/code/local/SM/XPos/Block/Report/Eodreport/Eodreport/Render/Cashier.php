<?php
/**
 * Created by PhpStorm.
 * User: Smartor
 * Date: 10/23/14
 * Time: 2:12 PM
 */

class SM_XPos_Block_Report_Eodreport_Eodreport_Render_Cashier extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row) {
        $cashierid = $row->getData('cashier_id');
        if (!empty($cashierid)) {
            $user = Mage::getModel('xpos/user')->load($cashierid);
            return $user->getData('firstname')." ". $user->getData('lastname');
        }
        else{
            return 'NO Cashier';
        }
    }
}