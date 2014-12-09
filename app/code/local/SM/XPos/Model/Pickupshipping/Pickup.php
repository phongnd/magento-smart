<?php
/**
 * Created by PhpStorm.
 * User: Smartor
 * Date: 10/14/14
 * Time: 2:13 PM
 */
class SM_XPos_Model_Pickupshipping_Pickup extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = 'pickup_shipping';

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $result = Mage::getModel('shipping/rate_result');
        /* @var $result Mage_Shipping_Model_Rate_Result */

        if($this->getConfigFlag('frontend_enabled') || Mage::app()->getStore()->isAdmin()) {
            $result->append($this->_getStandardShippingRate());
        }

        return $result;
    }

    protected function _getStandardShippingRate()
    {
        $rate = Mage::getModel('shipping/rate_result_method');
        /* @var $rate Mage_Shipping_Model_Rate_Result_Method */

        $rate->setCarrier($this->_code);
        /**
         * getConfigData(config_key) returns the configuration value for the
         * carriers/[carrier_code]/[config_key]
         */
        $rate->setCarrierTitle($this->getConfigData('title'));

        $rate->setMethod($this->_code);
        $rate->setMethodTitle($this->getConfigData('name'));
        $price = floatval($this->getConfigData('pickup_fee'));
        $rate->setPrice($price > 0 ? $price : 0);
        $rate->setCost(0);

        return $rate;
    }

    public function getAllowedMethods()
    {
        return array(
            'pickup_shipping' => 'Standard',
            'express' => 'Express',
        );
    }

    protected function _getExpressShippingRate()
    {
        $rate = Mage::getModel('shipping/rate_result_method');
        /* @var $rate Mage_Shipping_Model_Rate_Result_Method */
        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod('express');
        $rate->setMethodTitle('Express (Next day)');
        $rate->setPrice(12.99);
        $rate->setCost(0);
        return $rate;
    }


}