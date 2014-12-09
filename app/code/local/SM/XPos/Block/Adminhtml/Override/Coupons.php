<?php
class SM_XPos_Block_Adminhtml_Override_Coupons extends Mage_Adminhtml_Block_Sales_Order_Create_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_create_coupons_form');
    }

    public function getCouponCode()
    {        
        return $this->_getSession()->getQuote()->getCouponCode();
    }

}
?>
