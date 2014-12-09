<?php
class SM_XPos_Block_Adminhtml_Override_Totals extends Mage_Adminhtml_Block_Sales_Order_Create_Totals
{
    public function getTotalData($total_code){
        $totals = $this->getTotals();
        $value = 0;
        if(!empty($totals[$total_code]) && $totals[$total_code] instanceof Varien_Object){
            return $totals[$total_code]->getData('value');
        }
        return $value;
    }
}

