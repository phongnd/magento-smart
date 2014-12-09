<?php
/**
 * Created by PhpStorm.
 * User: Smartor
 * Date: 6/26/14
 * Time: 11:04 AM
 */
class SM_XPos_Block_Adminhtml_Sales_Order_Create_Giftcards extends Mage_Core_Block_Template{
    /**
     * Retrieve order create model
     *
     * @return Mage_Adminhtml_Model_Sales_Order_Create
     */
    protected function _getOrderCreateModel()
    {
        return Mage::getSingleton('adminhtml/sales_order_create');
    }

    public function getGiftCards()
    {
        $result = array();
        $quote = $this->_getOrderCreateModel()->getQuote();
        $cards = Mage::helper('enterprise_giftcardaccount')->getCards($quote);
        foreach ($cards as $card) {
            $result[] = $card['c'];
        }
        return $result;
    }

    /**
     * Check whether quote uses gift cards
     *
     * @return bool
     */
    public function isUsed()
    {
        $quote = $this->_getOrderCreateModel()->getQuote();

        return ($quote->getGiftCardsAmount() > 0);
    }


    public function isFullyPaid()
    {
        $quote = $this->_getOrderCreateModel()->getQuote();
        if (!$quote->getGiftCardsAmount() || $quote->getBaseGrandTotal() > 0 || $quote->getCustomerBalanceAmountUsed() > 0) {
            return false;
        }

        return true;
    }
}