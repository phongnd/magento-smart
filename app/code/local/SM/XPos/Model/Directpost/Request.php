<?php
class SM_XPos_Model_Directpost_Request extends Varien_Object
{
    protected $_transKey = null;

    /**
     * Return merchant transaction key.
     * Needed to generate sign.
     *
     * @return string
     */
    protected function _getTransactionKey()
    {
        return $this->_transKey;
    }

    /**
     * Set merchant transaction key.
     * Needed to generate sign.
     *
     * @param string $transKey
     * @return Mage_Authorizenet_Model_Directpost_Request
     */
    protected function _setTransactionKey($transKey)
    {
        $this->_transKey = $transKey;
        return $this;
    }

    /**
     * Generates the fingerprint for request.
     *
     * @param string $merchantApiLoginId
     * @param string $merchantTransactionKey
     * @param string $amount
     * @param string $fpSequence An invoice number or random number.
     * @param string $fpTimestamp
     * @return string The fingerprint.
     */
    public function generateRequestSign($merchantApiLoginId, $merchantTransactionKey, $amount, $currencyCode, $fpSequence, $fpTimestamp)
    {
        if (phpversion() >= '5.1.2') {
            return hash_hmac("md5",
                $merchantApiLoginId . "^" .
                $fpSequence . "^" .
                $fpTimestamp . "^" .
                $amount . "^" .
                $currencyCode, $merchantTransactionKey
            );
        }

        return bin2hex(mhash(MHASH_MD5,
            $merchantApiLoginId . "^" .
            $fpSequence . "^" .
            $fpTimestamp . "^" .
            $amount . "^" .
            $currencyCode, $merchantTransactionKey
        ));
    }



    /**
     * Set entity data to request
     *
     * @param Mage_Sales_Model_Order $order
     * @param Mage_Authorizenet_Model_Directpost $paymentMethod
     * @return Mage_Authorizenet_Model_Directpost_Request
     */
    public function setDataFromOrder(Mage_Sales_Model_Order $order, Mage_Authorizenet_Model_Directpost $paymentMethod)
    {
        $payment = $order->getPayment();

        $this->setXFpSequence($order->getQuoteId());
        $this->setXInvoiceNum($order->getIncrementId());
        $this->setXAmount($payment->getBaseAmountAuthorized());
        $this->setXCurrencyCode($order->getBaseCurrencyCode());
        $this->setXTax(sprintf('%.2F', $order->getBaseTaxAmount()))
            ->setXFreight(sprintf('%.2F', $order->getBaseShippingAmount()));

        //need to use strval() because NULL values IE6-8 decodes as "null" in JSON in JavaScript, but we need "" for null values.
        $billing = $order->getBillingAddress();
        if (!empty($billing)) {
            $this->setXFirstName(strval($billing->getFirstname()))
                ->setXLastName(strval($billing->getLastname()))
                ->setXCompany(strval($billing->getCompany()))
                ->setXAddress(strval($billing->getStreet(1)))
                ->setXCity(strval($billing->getCity()))
                ->setXState(strval($billing->getRegion()))
                ->setXZip(strval($billing->getPostcode()))
                ->setXCountry(strval($billing->getCountry()))
                ->setXPhone(strval($billing->getTelephone()))
                ->setXFax(strval($billing->getFax()))
                ->setXCustId(strval($billing->getCustomerId()))
                ->setXCustomerIp(strval($order->getRemoteIp()))
                ->setXCustomerTaxId(strval($billing->getTaxId()))
                ->setXEmail(strval($order->getCustomerEmail()))
                ->setXEmailCustomer(strval($paymentMethod->getConfigData('email_customer')))
                ->setXMerchantEmail(strval($paymentMethod->getConfigData('merchant_email')));
        }

        $shipping = $order->getShippingAddress();
        if (!empty($shipping)) {
            $this->setXShipToFirstName(strval($shipping->getFirstname()))
                ->setXShipToLastName(strval($shipping->getLastname()))
                ->setXShipToCompany(strval($shipping->getCompany()))
                ->setXShipToAddress(strval($shipping->getStreet(1)))
                ->setXShipToCity(strval($shipping->getCity()))
                ->setXShipToState(strval($shipping->getRegion()))
                ->setXShipToZip(strval($shipping->getPostcode()))
                ->setXShipToCountry(strval($shipping->getCountry()));
        }

        $this->setXPoNum(strval($payment->getPoNumber()));

        return $this;
    }

    /**
     * Set sign hash into the request object.
     * All needed fields should be placed in the object fist.
     *
     * @return Mage_Authorizenet_Model_Directpost_Request
     */
    public function signRequestData()
    {
        $fpTimestamp = time();
        $hash = $this->generateRequestSign(
            $this->getXLogin(),
            $this->_getTransactionKey(),
            $this->getXAmount(),
            $this->getXCurrencyCode(),
            $this->getXFpSequence(),
            $fpTimestamp
        );
        $this->setXFpTimestamp($fpTimestamp);
        $this->setXFpHash($hash);
        return $this;
    }

    /**
     * Set paygate data to request.
     *
     * @param Mage_Authorizenet_Model_Directpost $paymentMethod
     * @return Mage_Authorizenet_Model_Directpost_Request
     */
    public function setConstantData(Mage_Authorizenet_Model_Directpost $paymentMethod)
    {
        if(Mage::getStoreConfig('payment/authorizenet/card_present')){
            $this->setXCpversion('1.0')
                ->setXResponseFormat('1');
        }
        else{
            $this->setXVersion('3.1')
                ->setXDelimData('FALSE')
                ->setXRelayResponse('TRUE');
        }
        $this->setXTestRequest($paymentMethod->getConfigData('test') ? 'TRUE' : 'FALSE');

        $this->setXLogin($paymentMethod->getConfigData('login'))
            ->setXType('AUTH_ONLY')
            ->setXMethod(Mage_Paygate_Model_Authorizenet::REQUEST_METHOD_CC)
            ->setXRelayUrl($paymentMethod->getRelayUrl());
        if(Mage::getStoreConfig('payment/authorizenet/card_present')){
            $this->_setTransactionKey($paymentMethod->getConfigData('trans_key'))
                ->setXMarketType('2')
                ->setXDeviceType('5');
        }
        else{
            $this->_setTransactionKey($paymentMethod->getConfigData('trans_key'));
        }
        return $this;
    }
}
