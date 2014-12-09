<?php
class SM_XPos_Model_Sales_Quote extends Mage_Sales_Model_Service_Quote
{
    protected function _validate()
    {
        $helper = Mage::helper('sales');
        if (!$this->getQuote()->isVirtual()) {
            $address = $this->getQuote()->getShippingAddress();
            $addressValidation = $address->validate();
            if ($addressValidation !== true) {
                Mage::throwException(
                        $helper->__('Please check shipping address information. %s', implode(' ', $addressValidation))
                );
            }
            
            //2011-12-09 HiepHM: No need to active Free Shipping in admin
//            $address->setShippingMethod('freeshipping_freeshipping');
//            $address->setShippingAmount(0);
            /*
            $method= $address->getShippingMethod();
            $rate  = $address->getShippingRateByCode($method);
            if (!$this->getQuote()->isVirtual() && (!$method || !$rate)) {
                Mage::throwException($helper->__('Please specify a shipping method.'));
            }
            */
        }
    
        $addressValidation = $this->getQuote()->getBillingAddress()->validate();
        if ($addressValidation !== true) {
            Mage::throwException(
                    $helper->__('Please check billing address information. %s', implode(' ', $addressValidation))
            );
        }
    
        if (!($this->getQuote()->getPayment()->getMethod())) {
            Mage::throwException($helper->__('Please select a valid payment method.'));
        }
    
        return $this;
    }
}