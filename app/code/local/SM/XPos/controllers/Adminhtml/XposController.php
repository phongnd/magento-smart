<?php

require_once(BP . DS . 'app' . DS . 'code' . DS . 'core' . DS . 'Mage' . DS . 'Adminhtml' . DS . 'controllers' . DS . 'Sales' . DS . 'Order' . DS . 'CreateController.php');

class SM_XPos_Adminhtml_XposController extends Mage_Adminhtml_Sales_Order_CreateController
{

    protected $_billingOrigin;
    protected $_validated = false;
    protected $_storeid;

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/sales/xpos/mainxpos');
    }

    protected function _getOrderCreateModel()
    {
        return Mage::getSingleton('xpos/sales_create');
    }

    protected function _initSession()
    {
        /**
         * Identify warehouse
         */

        $warehouseId = Mage::getSingleton('adminhtml/session')->getWarehouseId();
        $this->_getSession()->setWarehouseId((int) $warehouseId);
        return parent::_initSession();
    }

    protected function _construct()
    {
        parent::_construct();
        if (Mage::helper('xpos')->aboveVersion('1.6')) {
            $this->_billingOrigin['city'] = Mage::getStoreConfig(Mage_Shipping_Model_Config::XML_PATH_ORIGIN_CITY);
            $this->_billingOrigin['country_id'] = Mage::getStoreConfig(Mage_Shipping_Model_Config::XML_PATH_ORIGIN_COUNTRY_ID);
            $this->_billingOrigin['region'] = Mage::getStoreConfig(Mage_Shipping_Model_Config::XML_PATH_ORIGIN_REGION_ID);
            $this->_billingOrigin['postcode'] = Mage::getStoreConfig(Mage_Shipping_Model_Config::XML_PATH_ORIGIN_POSTCODE);
        } else {
            $this->_billingOrigin['city'] = Mage::getStoreConfig('shipping/origin/city');
            $this->_billingOrigin['country_id'] = Mage::getStoreConfig('shipping/origin/country_id');
            $this->_billingOrigin['region'] = Mage::getStoreConfig('shipping/origin/region_id');
            $this->_billingOrigin['postcode'] = Mage::getStoreConfig('shipping/origin/postcode');
        }
        $this->_storeid = Mage::getStoreConfig('xpos/general/storeid');
    }

    protected function _validateSecretKey()
    {
        return true;
    }

    protected function _checkLicense()
    {
        if (!Mage::helper('smcore')->checkLicense("xpos", Mage::getStoreConfig('xpos/general/key')) || !Mage::helper("xpos")->isEnableModule()) {
            return $this->_redirect("adminhtml/system_config/edit/section/xpos");
        } else {
            $this->_validated = true;
        }
    }

    protected function _initAction()
    {
        /*$this->loadLayout();
        return $this;*/
    }

    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);

        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        Mage::register('sales_order', $order);
        Mage::register('current_order', $order);
        return $order;
    }

    protected  function _initCreditmemo($update = false)
    {
        $this->_title($this->__('Sales'))->_title($this->__('Credit Memos'));

        $creditmemo = false;
        $creditmemoId = $this->getRequest()->getParam('creditmemo_id');
        $orderId = $this->getRequest()->getParam('order_id');
        if ($creditmemoId) {
            $creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemoId);
        } elseif ($orderId) {
            $data   = $this->getRequest()->getParam('creditmemo');
            $order  = Mage::getModel('sales/order')->load($orderId);
            $invoice = $this->_initInvoice($order);

            $savedData = $this->_getItemData();

            $qtys = array();
            $backToStock = array();
            foreach ($savedData as $orderItemId =>$itemData) {
                if (isset($itemData['qty'])) {
                    $qtys[$orderItemId] = $itemData['qty'];
                }
                if (isset($itemData['back_to_stock'])) {
                    $backToStock[$orderItemId] = true;
                }
            }
            $data['qtys'] = $qtys;

            $service = Mage::getModel('sales/service_order', $order);
            if ($invoice) {
                $creditmemo = $service->prepareInvoiceCreditmemo($invoice, $data);
            } else {
                $creditmemo = $service->prepareCreditmemo($data);
            }

            /**
             * Process back to stock flags
             */
            foreach ($creditmemo->getAllItems() as $creditmemoItem) {
                $orderItem = $creditmemoItem->getOrderItem();
                $parentId = $orderItem->getParentItemId();
                if (isset($backToStock[$orderItem->getId()])) {
                    $creditmemoItem->setBackToStock(true);
                } elseif ($orderItem->getParentItem() && isset($backToStock[$parentId]) && $backToStock[$parentId]) {
                    $creditmemoItem->setBackToStock(true);
                } elseif (empty($savedData)) {
                    $creditmemoItem->setBackToStock(Mage::helper('cataloginventory')->isAutoReturnEnabled());
                } else {
                    $creditmemoItem->setBackToStock(false);
                }
            }
        }

        $args = array('creditmemo' => $creditmemo, 'request' => $this->getRequest());
        Mage::dispatchEvent('adminhtml_sales_order_creditmemo_register_before', $args);

        Mage::register('current_creditmemo', $creditmemo);
        return $creditmemo;
    }

    protected function _initInvoice($order)
    {
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        if ($invoiceId) {
            $invoice = Mage::getModel('sales/order_invoice')
                ->load($invoiceId)
                ->setOrder($order);
            if ($invoice->getId()) {
                return $invoice;
            }
        }
        return false;
    }

    protected function _getItemData()
    {
        $data = $this->getRequest()->getParam('creditmemo');
        if (!$data) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        }

        if (isset($data['items'])) {
            $qtys = $data['items'];
        } else {
            $qtys = array();
        }
        return $qtys;
    }

    protected function _getItemQtys()
    {
        $data = $this->getRequest()->getParam('order');
        if (isset($data['items'])) {
            $qtys = $data['items'];
        } else {
            $qtys = array();
        }
        return $qtys;
    }

    protected function _processData($action = null){
        if ($action != 'save') {
            if (Mage::helper('xpos')->aboveVersion('1.6')) {
                $data = $this->getRequest()->getParams();
                $data1 = $data['order'];
                if (isset($data['coupon']['code']) && !empty($data['coupon']['code'])) {
                    $this->_getQuote()->setCouponCode(trim($data['coupon']['code']));
                    $this->_getOrderCreateModel()->getShippingAddress()->setCouponCode(trim($data['coupon']['code']));
                }
                $quote = $this->_getOrderCreateModel()->getQuote();
                if ($data['customer_id'] != "false" && intval($data['customer_id'])) {
                    $customer = Mage::getModel('customer/customer')->load($data['customer_id']);
                    if ($quote->getCustomerGroupId() == null) {
                        $quote->setCustomerGroupId(0);
                    }
                    $quote->assignCustomer($customer);
                    Mage::register('xpos_order', $customer->getData('group_id'));
                } else {
                    $quote->setCustomerId(0);
                    $quote->setCustomerTaxClassId(0);
                    $quote->setCustomerGroupId(0);
                    $quote->setCustomerEmail('');
                    $quote->setCustomerFirstname('');
                    $quote->setCustomerLastname('');
                    if (!isset($data['order']['shipping_method']) && !is_string($quote->getShippingAddress()->getShippingMethod())) {
                        $quote->getShippingAddress()->setShippingMethod('pickup_shipping_pickup_shipping');
                        Mage::register('shipping_method_des', 'XPOS');
                    }
                    $this->_getOrderCreateModel()->setRecollect(true);
                    Mage::register('xpos_order', 0);
                }
                parent::_processActionData($action);

                //code to update configurable product custom price
                $cur_quote = $quote = $this->_getOrderCreateModel()->getQuote()->getId();
                $items = Mage::getModel('sales/quote_item')->getCollection()
                    ->addFieldToFilter('quote_id',array('eq'=>$cur_quote))
                    ->getData();

                foreach($items as  $item){
                    if($item['parent_item_id']){

                        if($item['custom_price']){
                            $parent_item = Mage::getModel('sales/quote_item')
                                ->load($item['parent_item_id']);
                            if(($parent_item->getData('product_type') == 'configurable' || $parent_item->getData('product_type') == 'bundle') && is_null($parent_item->getData('custom_price'))){
                                $data = array();
                                $info = array();
                                $info['qty'] = $parent_item->getData('qty');
                                $info['custom_price'] = $item['custom_price'];
                                $data[$item['parent_item_id']] = $info;
                                $data = $this->_processFiles($data);
                                $this->_getOrderCreateModel()->updateQuoteItemsConfig($data);
                            }
                        }
                    }
                }

            } else {
                parent::_processData();
            }
        }
        if ($action == 'save') {
            $quote = $this->_getOrderCreateModel()->getQuote();
            if ($storeid = $this->getRequest()->getPost('store_id'))
                $quote->setStoreId($storeid);
            else
                $quote->setStoreId($this->_storeid);

            $data = $this->getRequest()->getPost('order');
            $couponCode = $this->_processDiscount($data);

            $this->_getOrderCreateModel()->initRuleData();
            if (isset($data['customer_id']) && intval($data['customer_id'])) {
                $this->_getSession()->setCustomerId((int)$data['customer_id']);
                $customer = Mage::getModel('customer/customer')->load($data['customer_id']);
                $quote->assignCustomer($customer);
                Mage::register('xpos_order', $customer->getData('group_id'));
            } else {
                $quote->setCustomerGroupId(0);
                Mage::register('xpos_order', 0);
            }


            $billingAddress = array(
                'firstname' => 'Guest ' . $quote->getId(),
                'lastname' => 'Guest ' . $quote->getId(),
                'street' => array('Guest Address'),
                'city' => (empty($this->_billingOrigin['city']) ? 'Guest City' : $this->_billingOrigin['city']),
                'country_id' => (empty($this->_billingOrigin['country_id']) ? 'Guest City' : $this->_billingOrigin['country_id']),
                'region' => (empty($this->_billingOrigin['region']) ? 'CA' : $this->_billingOrigin['region']),
                'postcode' => (empty($this->_billingOrigin['postcode']) ? '95064' : $this->_billingOrigin['postcode']),
                'telephone' => (Mage::getStoreConfig('general/store_information/phone') == "" ? '1234567890' : Mage::getStoreConfig('general/store_information/phone')),
            );

            //EXIST CUSTOMER
            if (!empty($data['customer_id']) && ($customer_id = $this->_getSession()->getCustomerId())) {
                if ($customer->getDefaultBillingAddress())
                    $defaultBillingAddress = $customer->getDefaultBillingAddress()->getData();
                foreach ($billingAddress as $k => $v) {
                    if (empty($data['billing_address'][$k]))
                        $data['billing_address'][$k] = $defaultBillingAddress[$k];
                }

                if (!empty($defaultBillingAddress['region_id'])) {
                    unset($data['billing_address']['region']);
                    $data['billing_address']['region_id'] = $defaultBillingAddress['region_id'];
                }
            } else { //GUEST
                foreach ($billingAddress as $k => $v) {
                    if (empty($data['billing_address'][$k]))
                        $data['billing_address'][$k] = $v;

                    if (empty($data['shipping_address'][$k]))
                        $data['shipping_address'][$k] = $v;
                }
            }
            //Set default region_id
            if (empty($data['billing_address']['region_id'])) $data['billing_address']['region_id'] = 12;
            if (empty($data['shipping_address']['region_id'])) $data['shipping_address']['region_id'] = 12;

            $shippingAddress = $quote->getShippingAddress();
            $billingAddress = $quote->getBillingAddress();

            $billingAddress->addData($data['billing_address']);
            $shippingAddress->addData($data['shipping_address']);
            $shippingAddress->setStreet($shippingAddress->getStreet());
            $billingAddress->setStreet($billingAddress->getStreet());


            if (!isset($data['shipping_method']))
                $data['shipping_method'] = "freeshipping_freeshipping";




            $shippingAddress->setCollectShippingRates(true)->collectShippingRates()
                ->setShippingMethod($data['shipping_method']);

            if ($paymentData = $this->getRequest()->getPost('payment')) {
                $this->_getOrderCreateModel()->setPaymentData($paymentData);
                $quote->getPayment()->addData($paymentData);
            }
            if(!$paymentData = $this->getRequest()->getPost('payment')){
                $paymentData = array();
                $paymentData['method']= 'checkmo';
                $this->_getOrderCreateModel()->setPaymentData($paymentData);
                $quote->getPayment()->addData($paymentData);
            }
            $this->_getOrderCreateModel()->collectRates();
            $quote->collectTotals();

            $quote->save();

            // remove coupon if has
            if ($couponCode) {
                $couponCode->setIsActive(0)->save();
            }
        }
    }

    public function getIdQuoteAction(){
        $id_quote = $this->getRequest()->getPost('quote_item_id');
        $quote_item = Mage::getModel('sales/quote_item')->load($id_quote);
        //  echo "<pre>";
        //var_dump($quote_item->getData());die;
        $product_id = $quote_item->getData('product_id');
        $result =array();
        $result['product_id']=$product_id;
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function calDiscountAction($data){
        $this->_processDiscount($data);
    }

    protected function _processDiscount($data){
        $couponCode = false;
        $customDiscount = floatval($this->getRequest()->getPost('custom-discount'));
        $quote = $this->_getOrderCreateModel()->getQuote();

        if ($customDiscount > 0) {
            $model = Mage::getModel('salesrule/coupon');
            $name = 'X-POS #' . $quote->getId();
            $couponCode = substr(md5($name . microtime()), 0, 8);
            //$website_id = intval(Mage::getModel('core/store')->load(Mage::getStoreConfig('xpos/general/storeid'))->getWebsiteId());
           // $website_id = Mage::app()->getWebsite()->getId();
            // create coupon
            $currentTimestamp = Mage::getModel('core/date')->timestamp(time());
            $date = date('Y-m-d', $currentTimestamp);
            $rule = Mage::getModel('salesrule/rule')
                ->setName($name)
                ->setDescription('Coupon for X-POS')
                ->setCustomerGroupIds($this->_getCustomerGroups())
                ->setFromDate($date)
                ->setUsesPerCoupon(1)
                ->setUsesPerCustomer(1)
                ->setIsActive(1)
                ->setSimpleAction(Mage_SalesRule_Model_Rule::CART_FIXED_ACTION)
                ->setDiscountAmount($customDiscount)
                ->setStopRulesProcessing(0)
                ->setIsRss(0)
                ->setWebsiteIds(1)
                ->setCouponType(Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC)
                ->save();

            $model->setRuleId($rule->getId())
                ->setCode($couponCode)
                ->setIsPrimary(1)
                ->save();
        }
        if ($couponCode) {
            $data['coupon']['code'] = $couponCode;
            $this->_getOrderCreateModel()
                ->importPostData($data);
        }
        $result =array();
        $result['new_discount']=$customDiscount;
        $result['couponCode'] =$couponCode;
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        return $couponCode ? $rule : false;
    }

    protected function _getCustomerGroups(){
        $groupIds = array();
        $collection = Mage::getModel('customer/group')->getCollection();
        foreach ($collection as $customer) {
            $groupIds[] = $customer->getId();
        }

        return $groupIds;
    }

    protected function _clear_current_order_cache()
    {
        unset($_SESSION['xpos_loaded_order_id']);
    }

    public function indexAction()
    {
        $this->_checkLicense();
        $orderId = $this->getRequest()->getParam('order_id');

        if(Mage::getSingleton('adminhtml/sales_order_create')->getQuote()->getId() == null){
            $this->clearAction();
        }

        if ($orderId) {
            Mage::getSingleton('adminhtml/session')->setOrderViewDetail(NULL);
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->getId()) {
                //$this->_getSession()->clear();
                $order->setReordered(true);
                $this->_getOrderCreateModel()->initFromOrder($order);
                if( $order->getData('status') == 'pending' || $order->getData('state') == 'pending' ){
                    $order->setData('state',"closed");
                    $order->setStatus("closed");
                }
                $order->save();
            }
        }else{
            $this->_getSession()->setStoreId($this->_storeid);
            $this->_getSession()->setCustomerId(false);
            $_quoteId = $this->_getOrderCreateModel()->getQuote()->getId();
            $this->_getOrderCreateModel()->getQuote()->setStoreId($this->_storeid);
            $data_default = array(
                'account' => array(
                    'group_id' => 1
                ),
                'billing_address' => array(
                    'firstname' => 'Guest ' . $_quoteId,
                    'lastname' => 'Guest ' . $_quoteId,
                    'street' => array('Guest Address'),
                    'city' => (empty($this->_billingOrigin['city']) ? 'Guest City' : $this->_billingOrigin['city']),
                    'country_id' => (empty($this->_billingOrigin['country_id']) ? 'Guest City' : $this->_billingOrigin['country_id']),
                    'region' => (empty($this->_billingOrigin['region']) ? 'CA' : $this->_billingOrigin['region']),
                    'postcode' => (empty($this->_billingOrigin['postcode']) ? '95064' : $this->_billingOrigin['postcode']),
                    'telephone' => (Mage::getStoreConfig('general/store_information/phone') == "" ? '1234567890' : Mage::getStoreConfig('general/store_information/phone')),
                )
            );

            $this->_getOrderCreateModel()->importPostData($data_default);
            $this->_getOrderCreateModel()->setShippingAsBilling('on');
            $this->_getOrderCreateModel()->resetShippingMethod(true);
            $this->_getOrderCreateModel()->collectShippingRates();
            $this->_getOrderCreateModel()
                ->saveQuote();
        }
        $this->loadLayout();

        $_SESSION['blankcart'] = true;
        if (count($this->_getOrderCreateModel()->getQuote()->getAllItems()) > 0) {
            $_SESSION['blankcart'] = false;
        }

        $this->_initAction();
        $this->renderLayout();

    }

    public function checkOrderAction(){
        $this->_clear_current_order_cache();
        try{
            $data = $this->getRequest()->getPost('order');
            $data_all = $this->getRequest()->getPost();
            $customer_name = $data['billing_address']['firstname'] . " " . $data['billing_address']['lastname'];

            $this->_processData('save');

            $quote = $this->_getOrderCreateModel()->getQuote();

            if (!isset($data['shipping_address']['region_id'])) {
                $data['shipping_address']['region_id'] = Mage::getStoreConfig('xpos/guest/region_id');
                $data['billing_address']['region_id'] = $data['shipping_address']['region_id'];
            }

            if (empty($data['account']['group_id']) && !empty($data['customer_id'])) {
                $customer = Mage::getModel('customer/customer')->load($data['customer_id']);
                if ($customer->getId()) {
                    $data['account']['group_id'] = $customer->getGroupId();
                }
            }

            $data['billing_address']['group_id'] = $data['account']['group_id'];

            if ($data['account']['type'] == 'new') {
                $data['account']['email'] = $data['account']['email_temp'];
                $customer_name = $data['billing_address']['firstname'] . " " . $data['billing_address']['lastname'];
                $this->_getSession()->setCustomerId(0);
            }

            if ($data['account']['type'] == 'exist') {
                $customer_name = Mage::getModel('customer/customer')->load($data['customer_id'])->getName();
            }

            if (!isset($data['account']['gender']) || empty($data['account']['gender'])) {
                $data['account']['gender'] = 123; //123 = Male; 124 = Female
            }

            if (!isset($data['shipping_method']) || $data['shipping_method'] == '') {
                $data['shipping_method'] = "freeshipping_freeshipping";
            }
            $this->_getOrderCreateModel()->setRecollect(true);
            // fixed : cant save cc number when using authorise.net
            if ($paymentData = $this->getRequest()->getPost('payment')) {
                $this->_getOrderCreateModel()->setPaymentData($paymentData);
                $quote->getPayment()->addData($paymentData);
            }

            $quote->setCustomerIsGuest(0);
            Mage::unregister('pos_shipping_method');
            Mage::register('pos_shipping_method', $data['shipping_method']);
            $order = $this->_getOrderCreateModel()
                ->setIsValidate(true)
                ->importPostData($data)
                ->createOrder();

            /* Complete the order */
            $totalPaid = $this->getRequest()->getPost('cash-in');
            $totalRefunded = $this->getRequest()->getPost('balance');
            $creditStore = 0;
            $giftCard = 0;

            if(Mage::helper('xpos/data')->checkEE()){
                $creditStore = $quote->getData('customer_balance_amount_used');
                $giftCard = $quote->getData('gift_cards_amount');
            }

            $order_discount = $this->getRequest()->getPost('order_discount');
            if($order_discount < 0){
                $value = -$order_discount;
            }
            else{
                $value = $order_discount;
            }
            $grand_total = $order->getTaxAmount() + $order->getShippingAmount() + $order->getSubtotal() - $value - $creditStore - $giftCard;
//            $grand_total = $totalPaid;
//            $grand_total = $totalPaid - $data_all['balance'];
            //$order->setData('total_paid',0)->save();
            //$order->setData('total_paid',$grand_total)->save();
            $tax_include = Mage::getStoreConfig('tax/calculation/shipping_includes_tax');
            if($tax_include ==1 ){
                $tax_shipping = $order->getShippingTaxAmount();
                $grand_total = $grand_total - $tax_shipping;
            }
            $order
                ->setGrandTotal($grand_total)
                //->setTotalPaid($grand_total)
                //->setBaseTotalPaid($totalPaid)
               // ->setTotalRefunded($totalRefunded)
               // ->setBaseTotalRefunded($totalRefunded)
                ->save();


            $info_order['entity_id'] = $order->getEntityId();
            $info_order['order_id'] = $order->getIncrementId();
            $info_order['totalPaid'] = $totalPaid;
            $info_order['sub_total'] = $order->getSubtotal();
            $info_order['grand_total'] = $grand_total;
            $info_order['tax_amount'] = $order->getTaxAmount();
            $info_order['balance'] = $totalRefunded;
            //$info_order['balance'] = $data_all['balance'];
            $info_order['ship_amount'] = $order->getData('base_shipping_amount');
            $info_order['customer_name'] = $customer_name;

            Mage::register('info_order', $info_order);
            Mage::getSingleton('adminhtml/session')->setInfoOrder($info_order);

            $this->getResponse()->setBody(json_encode($info_order));

        }catch(Exception $e){
            $this->_getSession()->addError($e->getMessage());
            $this->getResponse()->setBody(0);
        }
    }

    public function completeAction(){
        $this->_clear_current_order_cache();
        try {
            $data = $this->getRequest()->getPost('order');
            $data_all = $this->getRequest()->getPost();

            $paymentData = $this->getRequest()->getPost('payment');

            $order = Mage::getModel('sales/order')->load($data_all['sm_order_id']);

            $quote_data = $this->_getOrderCreateModel()->getQuote();

            $cash_in = $totalPaid = $this->getRequest()->getPost('cash-in');
            $totalRefunded = $this->getRequest()->getPost('balance');
            $creditStore = $quote_data->getData('customer_balance_amount_used');
            $giftCard = $quote_data->getData('gift_cards_amount');

            $order_discount = $order->getDiscountInvoiced();
            if($order_discount < 0){
                $value = -$order_discount;
            }
            else{
                $value = $order_discount;
            }
            $grand_total = $order->getTaxAmount() + $order->getShippingAmount() + $order->getSubtotal() - $value - $creditStore - $giftCard;

            /* Create Invoice & Shipment */
            $canShip = false;
            $itemCollection = $quote_data->getItemsCollection();
            $authorize_value = Mage::getStoreConfig('payment/authorizenet/payment_action');

            foreach($itemCollection as $item) {
                $isVirtual = $item->getData('is_virtual');
                if (empty($isVirtual)) {
                    $canShip = true;
                }
            }

            if ($canShip && $data_all['doshipment']) {
                $savedQtys = $this->_getItemQtys();
                $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($savedQtys);
                $shipment->register();

                Mage::getModel('core/resource_transaction')
                    ->addObject($order)
                    ->addObject($shipment)
                    ->save();
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
            }

            if  ($data_all['doinvoice']) {
                $savedQtys = $this->_getItemQtys();
                if($order->getPayment()->getMethodInstance()->getCode() != "authorizenet"){
                    $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($savedQtys);
                    $invoice->register();
                    Mage::getModel('core/resource_transaction')
                        ->addObject($invoice)
                        ->addObject($order)
                        ->save();
                    $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
                    $order->setTotalPaid(0)->save();
                    $order->setTotalPaid($order->getData('grand_total'))
                        ->setBaseTotalPaid($order->getData('grand_total'))
                        ->save();
                }
                else{
                    if ($authorize_value != "authorize_capture" && $authorize_value != "authorize")
                    {
                        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($savedQtys);
                        $invoice->register();
                        Mage::getModel('core/resource_transaction')
                            ->addObject($invoice)
                            ->addObject($order)
                            ->save();
                        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
                        $order->setTotalPaid(0)->save();
                        $order->setTotalPaid($order->getData('grand_total'))
                            ->setBaseTotalPaid($order->getData('grand_total'))
                            ->save();
                    }
                    else
                    {
                        Mage::getModel('core/resource_transaction')
                            ->addObject($order)
                            ->save();
                    }
                }
            }

            $default_address = array(
                'firstname' => 'Guest X-Pos',
                'lastname' => 'Guest X-Pos',
                'street' => array('Guest Address'),
                'city' => (empty($this->_billingOrigin['city']) ? 'Guest City' : $this->_billingOrigin['city']),
                'country_id' => (empty($this->_billingOrigin['country_id']) ? 'Guest City' : $this->_billingOrigin['country_id']),
                'region' => (empty($this->_billingOrigin['region']) ? 'CA' : $this->_billingOrigin['region']),
                'postcode' => (empty($this->_billingOrigin['postcode']) ? '95064' : $this->_billingOrigin['postcode']),
                'telephone' => (Mage::getStoreConfig('general/store_information/phone') == "" ? '1234567890' : Mage::getStoreConfig('general/store_information/phone')),
            );

            /*
             * Compare customer address with default.
             * If the address is not same as default, Then comare with customer's exist addresses
             */

            $isCreateNewBillingAddress = false;
            $isCreateNewShippingAddress = false;
            if (is_array($data['billing_address'])) {
                if(count(array_diff($default_address,$data['billing_address'])) != 0){ //Different
                    $isCreateNewBillingAddress = true;
                }
            }

            if (is_array($data['shipping_address'])) {
                if(count(array_diff($default_address,$data['shipping_address'])) != 0){ //Different
                    $isCreateNewShippingAddress = true;
                }
            }

            /**
             * Compare POST/SEND(from XPOS) address with customer's exist addresses
             */
            $remove_customer_address_fields = array(
                'entity_id',
                'entity_type_id',
                'attribute_set_id',
                'increment_id',
                'parent_id',
                'created_at',
                'updated_at',
                'is_active',
                'customer_id',
                'vat_id',
                'fax',
                'company',
                'suffix',
                'middlename',
                'prefix',
                'region_id',
                'region'


            );
            $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
            $customer_address_list = array();
            $customer_address_list['billing_address'] = array();
            $customer_address_list['shipping_address'] = array();

            $check_billing_address = $data['billing_address'];
            $check_billing_address['street'] = $data['billing_address']['street'][0];
            unset($data['billing_address'][0]);
            unset($check_billing_address['region_id']);
            unset($check_billing_address['region']);

            $check_shipping_address = $data['shipping_address'];
            $check_shipping_address['street'] = $data['shipping_address']['street'][0];
            unset($data['shipping_address'][0]);
            unset($check_shipping_address['region_id']);
            unset($check_shipping_address['region']);

            if(count($customer->getAddressesCollection())>0){
                foreach($customer->getAddressesCollection() as $address){
                    $customer_address = $address->getData();
                    foreach($remove_customer_address_fields as $remove_field){
                        unset($customer_address[$remove_field]);
                    }

                    $check_exist_billing_address = array_diff_assoc($check_billing_address,$customer_address);

                    if(!empty($check_exist_billing_address)){//Different
                        $customer_address_list['billing_address'][] = 'new'; //means need to create
                    } else{
                        $customer_address_list['billing_address'][] = 'exist'; // means that It is existing in addresses list
                    }

                    $check_exist_shipping_address = array_diff_assoc($check_shipping_address,$customer_address);
                    if(!empty($check_exist_shipping_address)){//Different
                        $customer_address_list['shipping_address'][] = 'new'; // means need to create
                    } else{
                        $customer_address_list['shipping_address'][] = 'exist'; // means that It is existing in addresses list
                    }
                }
            }

            if(in_array('exist',$customer_address_list['billing_address'])){
                $isCreateNewBillingAddress = false;
            } else{
                $isCreateNewBillingAddress = true;
            }

            if(in_array('exist',$customer_address_list['shipping_address'])){
                $isCreateNewShippingAddress = false;
            }else{
                $isCreateNewShippingAddress = true;
            }

            /**
             * Set/Save default shipping address.
             * If not exist address. This will add a new none, else it will remain.
             */
            if($isCreateNewBillingAddress){
                $customAddress = Mage::getModel('customer/address');
                $customAddress->setData($data['billing_address'])
                    ->setCustomerId($order->getCustomerId())
                    ->setIsDefaultBilling('1');
                try {
                    $customAddress->save();
                    $customAddress->setConfirmation(null);
                    $customAddress->save();
                }
                catch (Exception $ex) {
                }
                //$quote->setBillingAddress(Mage::getSingleton('sales/quote_address')->importCustomerAddress($customAddress));
            }


            /**
             * Set/Save default shipping address
             */
            if($isCreateNewShippingAddress){
                $customAddress = Mage::getModel('customer/address');
                $customAddress->setData($data['shipping_address'])
                    ->setCustomerId($order->getCustomerId())
                    ->setIsDefaultShipping('1');
                try {
                    $customAddress->save();
                    $customAddress->setConfirmation(null);
                    $customAddress->save();
                }
                catch (Exception $ex) {
                }
                //$quote->setBillingAddress(Mage::getSingleton('sales/quote_address')->importCustomerAddress($customAddress));
            }

            /*
             * END PROCESS AND CREATE BILLING, SHIPPING ADDRESS
             */

            $payment_method = $paymentData['method'];
            if (($order->getIncrementId())) {
                $data_transaction = array(
                    'type' => $payment_method,
                    'cash_in' => $cash_in,
                    'cash_out' => $totalRefunded,
                    'amount' => $grand_total,
                    'till_id' => $data_all['till_id'],
                    'warehouse_id' => $data_all['warehouse_id'],
                    'xpos_user_id' => $data_all['xpos_user_id'],
                    'order_id' => $order->getIncrementId(),
                );

                switch ($payment_method) {
                    case 'checkmo':
                        $data_transaction['payment_method'] = 'Check Money Order';
                        break;
                    case 'cashpayment':
                        $data_transaction['payment_method'] = 'X-Pos Cash';
                        break;
                    case 'cc':
                        $data_transaction['payment_method'] = 'CC';
                        break;
                    case 'epay_standard':
                        $data_transaction['payment_method'] = 'ePay';
                        break;
                    default:
                        $data_transaction['payment_method'] = 'N/a';
                        break;
                }

                Mage::getModel('xpos/transaction')->saveTransaction($data_transaction);
            }

            $this->_getSession()->clear();

            if (Mage::helper('xpos/data')->isEmailConfirmationEnabled()) {

                /* Sending email | Send email */
                $doEmailReceipt = $this->getRequest()->getParam('doemailreceipt');
                $email = $this->getRequest()->getParam('emailreceipt');
                if(empty($email))
                    $email = $this->getRequest()->getParam('tempemailreceipt');

                $isSendEmailCopy = true;
                if (!empty($doEmailReceipt) && !empty($email)) {
//                    try {
                    $mailer = Mage::getModel('core/email_template_mailer');
                    $storeId = $order->getStore()->getId();
                    if ($data['account']['type'] == 'guest') { //$order->getCustomerIsGuest()
                        $templateId = Mage::getStoreConfig('xpos/receipt/guest_template', $storeId);
                        if ($templateId == 'xpos_receipt_guest_template' || empty($templateId)) {
                            //sales_email_invoice_guest_template
                            $templateId = Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId);
                        }
                        $customerName = $order->getBillingAddress()->getName();
                    } else {
                        $templateId = Mage::getStoreConfig('xpos/receipt/customer_template', $storeId);
                        if ($templateId == 'xpos_receipt_customer_template' || empty($templateId)) { //Set default template
                            //sales_email_invoice_template
                            $templateId = Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_TEMPLATE, $storeId);
                        }
                        $customerName = $order->getCustomerName();
                    }

                    $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                        ->setIsSecureMode(true);
                    $paymentBlock->getMethod()->setStore($storeId);
                    $paymentBlockHtml = $paymentBlock->toHtml();


                    $emailInfo = Mage::getModel('core/email_info');
                    $emailInfo->addTo($email, $customerName);

                    //Send BCC.
                    if (Mage::getStoreConfig('xpos/receipt/copy_method') == 'bcc') {
                        $isSendEmailCopy = false;
                        $copy_emails = Mage::getStoreConfig('xpos/receipt/sales_emails');
                        $copy_emails = explode(',', $copy_emails);
                        if (is_array($copy_emails) && count($copy_emails) > 0) {
                            foreach ($copy_emails as $copy_email) {
                                $copy_email = trim(strip_tags($copy_email));
                                //$mailer->addBcc($copy_email);
                                $emailInfo->addBcc($copy_email);
                            }
                        }
                    }

                    $mailer->addEmailInfo($emailInfo);

                    //Set sender: sendersales
                    $sender = Mage::getStoreConfig('xpos/receipt/sender');
                    if (empty($sender)) {
                        //sales
                        $sender = Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_IDENTITY, $storeId);
                    }

                    $mailer->setTemplateId($templateId);
                    $mailer->setSender($sender);
                    $mailer->setStoreId($storeId);

                    $mailer->setTemplateParams(array(
                            'order' => $order,
                            'invoice' => $invoice,
                            'comment' => '',
                            'billing' => $order->getBillingAddress(),
                            'payment_html' => $paymentBlockHtml
                        )
                    );
                    $mailer->send();

//                    } catch (Exception $e) {
//                        Mage::logException($e);
//                        $this->_getSession()->addError($this->__('Unable to send the invoice email to ' . $email));
//                    }
                }

                if (($sale_emails = Mage::getStoreConfig('xpos/receipt/sales_emails') !='') && ($isSendEmailCopy == true) && !empty($email)) {
                    $sale_emails = Mage::getStoreConfig('xpos/receipt/sales_emails');
                    $sale_emails = explode(',', $sale_emails);

                    if (is_array($sale_emails) && count($sale_emails) > 0) {
                        //Send email to Sales team
                        foreach ($sale_emails as $sale_email) {
                            $sale_email = trim(strip_tags($sale_email));
                            if (filter_var($sale_email, FILTER_VALIDATE_EMAIL)) {
                                try {
                                    $mailer = Mage::getModel('core/email_template_mailer');
                                    $storeId = $order->getStore()->getId();
                                    $templateId = Mage::getStoreConfig('xpos/receipt/customer_template', $storeId);
                                    if ($templateId == 'xpos_receipt_customer_template' || empty($templateId)) {
                                        $templateId = Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_TEMPLATE, $storeId);
                                    }
                                    $customerName = $sale_email;

                                    $mailer->setTemplateId($templateId);

                                    $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                                        ->setIsSecureMode(true);
                                    $paymentBlock->getMethod()->setStore($storeId);
                                    $paymentBlockHtml = $paymentBlock->toHtml();


                                    $emailInfo = Mage::getModel('core/email_info');
                                    $emailInfo->addTo($sale_email, $customerName);

                                    $mailer->addEmailInfo($emailInfo);

                                    //Set sender
                                    $sender = Mage::getStoreConfig('xpos/receipt/sender');
                                    if (empty($sender)) {
                                        $sender = Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_IDENTITY, $storeId);
                                    }
                                    $mailer->setSender($sender);
                                    $mailer->setStoreId($storeId);

                                    $mailer->setTemplateParams(array(
                                            'order' => $order,
                                            'invoice' => $invoice,
                                            'comment' => '',
                                            'billing' => $order->getBillingAddress(),
                                            'payment_html' => $paymentBlockHtml
                                        )
                                    );
                                    if (Mage::getStoreConfig('xpos/receipt/copy_method') != 'bcc')
                                        $mailer->send();

                                } catch (Exception $e) {
                                    Mage::logException($e);
                                    $this->_getSession()->addError($this->__('Unable to send the invoice email to ' . $sale_email));
                                }
                            }
                        }

                    }
                }


            }

            //delete customer if customer is guest
            if ($data['account']['type'] == 'guest') {
                $customer = Mage::getModel('customer/customer')->load($order->getData('customer_id'));
                $customer->delete();
                //Mage::getSingleton('core/session')->setCustomertype($data['account']['type']);
            }

            //Redirect to Paypal Here App
            if($paymentData['method']=='here'){
                $href = Mage::helper("adminhtml")->getUrl('*/xpos/paypalhere', array('order_id' => $order->getId()));
                Mage::getSingleton('core/session')->setOrderIncrementId($order->getIncrementId());
                Mage::getSingleton('core/session')->setPaypalhereUrl($href);
                Mage::log("PPhereURLCotroller=".$href);
                Mage::getSingleton('core/session')->setReturnUrl(Mage::helper('adminhtml')->getUrl('*/xpos/index'));
                //Redirect to paypalhere redirect page
                $this->_getSession()->addSuccess("Order #".$order->getIncrementId()." successfully created.");
                Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("*/xpos/paypalhere", array('order_id' => $order->getId())));
            } else{
                if ($data_all['doinvoice']) {
                    //$href = Mage::helper("adminhtml")->getUrl('*/xpos/print', array('order_id' => $order->getId()));
                    //$doPrintReceipt = $this->getRequest()->getPost('doprintreceipt');
                    //if (!empty($doPrintReceipt)) {
                    //    Mage::getSingleton('core/session')->setPrintUrl($href);
                    //}
                }
                $this->_getSession()->addSuccess("Order #".$order->getIncrementId()." successfully created.");
                $this->_redirect('*/xpos/index');
            }

            //Redirect to EPay
            if($paymentData['method']=='epay_standard'){
                $href = Mage::getUrl('epay/standard/redirect', array('order_id' => $order->getId()));
                Mage::getSingleton('core/session')->setOrderIncrementId($order->getIncrementId());
                //Mage::getSingleton('core/session')->setPaypalhereUrl($href);
                Mage::log("PPhereURLCotroller=".$href);
                Mage::getSingleton('core/session')->setReturnUrl(Mage::helper('adminhtml')->getUrl('*/xpos/index'));
                $quoteId = $quote_data->getId();
                $orderId = $order->getIncrementId();
                Mage::app()->getResponse()->setRedirect(Mage::getUrl("epay/standard/redirect", array('order_id' => $orderId,'quote_id' => $quoteId)));
            }

        } catch (Mage_Payment_Model_Info_Exception $e) {
            $this->_getOrderCreateModel()->saveQuote();
            $message = $e->getMessage();
            if (!empty($message)) {
                $this->_getSession()->addError($message);
            }
            $this->_redirect('*/*/');
        } catch (Mage_Core_Exception $e) {
            $message = $e->getMessage();
            if (!empty($message)) {
                $this->_getSession()->addError($message);
            }
            $this->_redirect('*/*/');
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Order saving error: %s', $e->getMessage()));
            $this->_redirect('*/*/');
        }
    }

    public function completeofflineAction()
    {
        $this->_clear_current_order_cache();

        try {
            $data = $this->getRequest()->getPost('order');
            $data_all = $this->getRequest()->getPost();
            $couponCode = $this->_processDiscount($data);
            $shipping_same_as_billing = $this->getRequest()->getPost('shipping_same_as_billing');
            // if no shipping method specified , then use free shipping method as default shipping method
            if (is_null(@$data['shipping_method'])) {
                //$data['shipping_method'] = "freeshipping_freeshipping";
                $data['shipping_method'] = "pickup_shipping_pickup_shipping";
            }
            if (($data['account']['type'] == 'new') || ($data['account']['type'] == 'exist')) {
                //$data['account']['email'] = $data['account']['email_temp'];
                $data['account']['email'] = $this->getRequest()->getPost('customer_email');
            }
            if (@$data['customer_id'] != "false" && intval(@$data['customer_id'])) {
                $this->_getSession()->setCustomerId((int)$data['customer_id']);
            } else {
                $this->_getSession()->setCustomerId(0);
            }

            $quote = $this->_getOrderCreateModel()->getQuote();
            // set store
            if ($storeid = $this->getRequest()->getPost('store_id'))
                $quote->setStoreId($storeid);
            else
                $quote->setStoreId($this->_storeid);
            // set billing address
            $billingAddress = $quote->getBillingAddress()->addData($data['billing_address']);
            $billingAddress = array(
                'firstname' => 'Guest ' . $quote->getId(),
                'lastname' => 'Guest ' . $quote->getId(),
                'street' => array('Guest Address'),
                'city' => (empty($this->_billingOrigin['city']) ? 'Guest City' : $this->_billingOrigin['city']),
                'country_id' => (empty($this->_billingOrigin['country_id']) ? 'Guest City' : $this->_billingOrigin['country_id']),
                'region' => (empty($this->_billingOrigin['region']) ? 'CA' : $this->_billingOrigin['region']),
                'postcode' => (empty($this->_billingOrigin['postcode']) ? '95064' : $this->_billingOrigin['postcode']),
                'telephone' => (Mage::getStoreConfig('general/store_information/phone') == "" ? '1234567890' : Mage::getStoreConfig('general/store_information/phone')),
                'region_id' => 12
            );
            $addressData = array(
                'firstname' => 'Guest ' . $quote->getId(),
                'lastname' => 'Guest ' . $quote->getId(),
                'street' => array('Guest Address'),
                'city' => 'Somewhere',
                'postcode' => '123456',
                'telephone' => '123456',
                'country_id' => 'US',
                'region_id' => 12, // id from directory_country_region table
            );

            if (!empty($data['customer_id']) && ($customer_id = $this->_getSession()->getCustomerId())) {
                $tmp = Mage::getModel('customer/customer')->load($customer_id)->getDefaultBillingAddress();
                if ($tmp)
                    $defaultBillingAddress = $tmp->getData();
                else
                    $defaultBillingAddress = $billingAddress;
                foreach ($billingAddress as $k => $v) {
                    if (!empty($defaultBillingAddress[$k]))
                        $data['billing_address'][$k] = $defaultBillingAddress[$k];
                }

                if (!empty($defaultBillingAddress['region_id'])) {
                    unset($data['billing_address']['region']);
                    $data['billing_address']['region_id'] = $defaultBillingAddress['region_id'];
                }
            } else {
                foreach ($billingAddress as $k => $v) {
                    if (empty($data['billing_address'][$k]))
                        $data['billing_address'][$k] = $v;

                    if (empty($data['shipping_address'][$k]))
                        $data['shipping_address'][$k] = $v;
                }
            }
            if (!isset($data['billing_address']['region_id']) || empty($data['billing_address']['region_id'])) $data['billing_address']['region_id'] = 12;
            if (!isset($data['shipping_address']['region_id']) || empty($data['shipping_address']['region_id'])) $data['shipping_address']['region_id'] = 12;

            $billingAddress = $quote->getBillingAddress()->addData($data['billing_address']);

            if ($data['account']['type'] == 'new') {
                $quote->setCustomerEmail($data['account']['email_temp']);
//                if ($shipping_same_as_billing) {
//
//                }
                $shippingAddress = $quote->getShippingAddress()->addData($data['billing_address']);
                $quote->save();
            }

//            if ($data['account']['type'] != 'guest' && !$shipping_same_as_billing) {
//                $shippingAddress = $quote->getShippingAddress()->addData($data['shipping_address']);
//            } else {
//                $shippingAddress = $quote->getShippingAddress()->addData($data['billing_address']);
//            }

            $shippingAddress = $quote->getShippingAddress()->addData($data['shipping_address']);

            if ($data['account']['type'] == 'exist') {
                $customer = Mage::getModel('customer/customer')
                    ->setWebsiteId(1)
                    ->loadByEmail($data['account']['email']);
                $quote->assignCustomer($customer);
                // billing address
//                if ($customer->getDefaultBillingAddress())
//                    $quote->getBillingAddress()->addData( $customer->getDefaultBillingAddress()->getData());

                if ($shipping_same_as_billing) {
                    if ($customer->getDefaultBillingAddress())
                        $addressData = $customer->getDefaultBillingAddress()->getData();
                    else {
                        $addressData['firstname'] = $customer->getFirstname();
                        $addressData['lastname'] = $customer->getLastname();
                    }
                } else {
                    $addressData = $data['shipping_address'];
                }
                $shippingAddress = $quote->getShippingAddress()->addData($addressData);
//                    $shippingAddress->setCollectShippingRates(true)->collectShippingRates()
//                            ->setShippingMethod('freeshipping_freeshipping');
            }
            if ($data['account']['type'] == 'guest') {
// for guest orders only:
                $quote->setCustomerEmail('guest' . $quote->getId() . '@example.com');
            }
            $quoteItems = $quote->getAllItems();
            foreach ($quoteItems as $item) {
                $quote->removeItem($item->getId());
            }
            $quoteItems = $this->getRequest()->getPost('item');
//$this->_getOrderCreateModel()->addProducts($quoteItems);
            $items = array();
            foreach ($quoteItems as $item => $itemdetails) {
                if (@$itemdetails['qty'] != 0)
                    $items[$item] = $itemdetails;
            }
            $items = $this->_processFiles($items);
            $this->_getOrderCreateModel()->addProducts($items);
            $this->_getOrderCreateModel()->updateCustomPrice($quoteItems);

///            $this->_getOrderCreateModel()->collectShippingRates();
            if ($paymentData = $this->getRequest()->getPost('payment')) {
                $this->_getOrderCreateModel()->setPaymentData($paymentData);
                $quote->getPayment()->addData($paymentData);
            }

            $this->_getOrderCreateModel()->collectRates();
            $this->_getOrderCreateModel()
                ->saveQuote();

            unset($data['shipping_address']);
            unset($data['billing_address']);
//            unset($data['customer_id']);
//            unset($data['account']);
//            $data['shipping_method'] = "freeshipping_freeshipping";
            $this->_getOrderCreateModel()->setRecollect(true);

            Mage::unregister('pos_shipping_method');
            Mage::register('pos_shipping_method', $data['shipping_method']);

            $order = $this->_getOrderCreateModel()
                ->setIsValidate(true)
                ->importPostData($data)
                ->createOrder();

            /* Create Invoice & Shipment */
            $savedQtys = $this->_getItemQtys();

            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($savedQtys);
            $invoice->register();

            $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($savedQtys);
            $shipment->register();
            /* Complete the order */
            $cash_in = $totalPaid = $this->getRequest()->getPost('cash-in');
            $totalRefunded = $this->getRequest()->getPost('balance');
            $creditStore = $order->getData('customer_balance_amount_used');
            $giftCard = $order->getData('gift_cards_amount');
            $grand_total = $order->getTaxAmount() + $order->getShippingAmount() + $order->getSubtotal() - $order->getDiscountInvoiced() - $creditStore - $giftCard;
            $order
                ->setGrandTotal($order->getGrandTotal() + $order->getShippingAmount())
                ->setTotalPaid($this->getRequest()->getPost('cash-in'))
                ->setBaseTotalPaid($this->getRequest()->getPost('cash-in'))
                ->setTotalRefunded($this->getRequest()->getPost('balance'))
                ->setBaseTotalRefunded($this->getRequest()->getPost('balance'));

            Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($order)
                ->addObject($shipment)->save();
            $payment_method = $this->getRequest()->getPost('payment');
            $payment_method = $payment_method['method'];
            if (($order->getId())) {
                $data_transaction = array(
                    'type' => $payment_method,
                    'cash_in' => $cash_in,
                    'cash_out' => $totalRefunded,
                    'amount' => $grand_total,
                    'till_id' => $data_all['till_id'],
                    'warehouse_id' => $data_all['warehouse_id'],
                    'xpos_user_id' => $data_all['xpos_user_id'],
                    'order_id' => $order->getIncrementId(),
                );

                switch ($payment_method) {
                    case 'checkmo':
                        $data_transaction['payment_method'] = 'Check Money Order';
                        break;
                    case 'cashpayment':
                        $data_transaction['payment_method'] = 'X-Pos Cash';
                        break;
                    case 'cc':
                        $data_transaction['payment_method'] = 'CC';
                        break;
                    case 'epay_standard':
                        $data_transaction['payment_method'] = 'ePay';
                        break;
                    default:
                        $data_transaction['payment_method'] = 'N/a';
                        break;
                }

                Mage::getModel('xpos/transaction')->saveTransaction($data_transaction);


            }
            $this->_getSession()->clear();

            //delete customer if customer is guest
            if ($data['account']['type'] == 'guest') {
                $customer = Mage::getModel('customer/customer')->load($order->getData('customer_id'));
                $customer->delete();
            }

            $result = array(
                'status' => 'success',
                'msg' => '',
                'printurl' => Mage::helper("adminhtml")->getUrl('*/xPos/print', array('order_id' => $order->getId()))
            );
            // remove coupon if has
            if ($couponCode) {
                $couponCode->setIsActive(0)->save();
            }
        } catch (Mage_Payment_Model_Info_Exception $e) {
            $this->_getOrderCreateModel()->saveQuote();
            $result['status'] = 'error';
            $result['msg'] = $e->getMessage();
        } catch (Mage_Core_Exception $e) {
            $result['status'] = 'error';
            $result['msg'] = $e->getMessage();
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['msg'] = $e->getMessage();
        }
        echo json_encode($result);die;
    }

    public function saveAction(){
        $this->_clear_current_order_cache();
        try {
            $this->_processData('save');
            $data = $this->getRequest()->getPost('order');

            if (!isset($data['billing_address']['region_id']) || empty($data['billing_address']['region_id'])) $data['billing_address']['region_id'] = 12;
            if (!isset($data['shipping_address']['region_id']) || empty($data['shipping_address']['region_id'])) $data['shipping_address']['region_id'] = 12;

            if (!isset($data['shipping_method']) || empty($data['shipping_method'])) {
                $data['shipping_method'] = "freeshipping_freeshipping";
            }
            Mage::unregister('pos_shipping_method');
            Mage::register('pos_shipping_method', $data['shipping_method']);
            $order = $this->_getOrderCreateModel()
                ->setIsValidate(true)
                ->importPostData($data)
                ->createOrder();

            $order
                ->setGrandTotal($order->getTaxAmount() + $order->getShippingAmount() + $order->getSubtotal());
            $order->save();


            if ($data['account']['email_temp'] && $data['account']['email_temp'] != "") {
                $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
                if($data['account']['email_temp']  == ""){
                    //$customer->setEmail($data['account']['email_temp'])->save();
                }else{
                    $customer->setEmail($data['account']['email_temp'])->save();
                }
            }
            $this->_getSession()->clear();

            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The order has been created.'));
            $this->_redirect('*/*/index');
        } catch (Mage_Payment_Model_Info_Exception $e) {
            $this->_getOrderCreateModel()->saveQuote();
            $message = $e->getMessage();
            if (!empty($message)) {
                $this->_getSession()->addError($message);
            }
            $this->_redirect('*/*/');
        } catch (Mage_Core_Exception $e) {
            $message = $e->getMessage();
            if (!empty($message)) {
                $this->_getSession()->addError($message);
            }
            $this->_redirect('*/*/');
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Order saving error: %s', $e->getMessage()));
            $this->_redirect('*/*/');
        }
    }

    public function printinvoiceAction()
    {
        $order_id = $this->getRequest()->getParam('order_id', 1);
        $this->_title("Invoice No." . $order_id);
        $this->loadLayout();
        $this->renderLayout();
    }

    public function printcreditmemoAction()
    {
//        $order_id = $this->getRequest()->getParam('order_id', 1);
//        $this->_title("Invoice No." . $order_id);
//        $this->loadLayout();
//        $this->renderLayout();

        $creditmemo = $this->_initCreditmemo();
        if ($creditmemo) {
            if ($creditmemo->getInvoice()) {
                $this->_title($this->__("View Memo for #%s", $creditmemo->getInvoice()->getIncrementId()));
            } else {
                $this->_title($this->__("View Memo"));
            }

            $this->loadLayout();
            $this->renderLayout();
        } else {
            echo 'Error when processing!!!!';
        }
    }


    public function clearAction()
    {
        $this->_clear_current_order_cache();
        $this->_getSession()->clear();
        $this->_redirect('*/*/');
    }

    public function loadBlockAction()
    {
        $order_id = $this->getRequest()->getParam('orderId');
        if($order_id != NULL) {
            Mage::getSingleton('adminhtml/session')->setOrderViewDetail($order_id);
        }

        $request = $this->getRequest();
        try {
            $this->_initSession()
                ->_processData();

        } catch (Mage_Core_Exception $e) {
            $this->_reloadQuote();
            $this->_getSession()->addError($e->getMessage());

        } catch (Exception $e) {
            $this->_reloadQuote();
            $this->_getSession()->addException($e, $e->getMessage());
        }

        $asJson = $request->getParam('json');
        $block = $request->getParam('block');

        if($block=='sales_creditmemo_create'){
            $creditmemo = $this->_initCreditmemo();
        }

        $update = $this->getLayout()->getUpdate();
        if ($asJson) {
            $update->addHandle('adminhtml_xpos_load_block_json');
        } else {
            $update->addHandle('adminhtml_xpos_load_block_plain');
        }

        if ($block) {
            $blocks = explode(',', $block);
            if ($asJson && !in_array('message', $blocks)) {
                $blocks[] = 'message';
            }

            foreach ($blocks as $block) {
                $update->addHandle('adminhtml_xpos_load_block_' . $block);
            }
        }
        $this->loadLayoutUpdates()->generateLayoutXml()->generateLayoutBlocks();
        $result = $this->getLayout()->getBlock('content')->toHtml();

        if ($request->getParam('as_js_varname')) {
            Mage::getSingleton('adminhtml/session')->setUpdateResult($result);
            $this->_redirect('*/*/showUpdateResult');
        } else {
            $this->getResponse()->setBody($result);
        }
    }

    public function addCommentAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                $response = false;
                $data = $this->getRequest()->getPost('history');
                $notify = isset($data['is_customer_notified']) ? $data['is_customer_notified'] : false;
                $visible = isset($data['is_visible_on_front']) ? $data['is_visible_on_front'] : false;

                $order->addStatusHistoryComment($data['comment'], $data['status'])
                    ->setIsVisibleOnFront($visible)
                    ->setIsCustomerNotified($notify);

                $comment = trim(strip_tags($data['comment']));

                $order->save();
                $order->sendOrderUpdateEmail($notify, $comment);

            }
            catch (Mage_Core_Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => $e->getMessage(),
                );
            }
            catch (Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => $this->__('Cannot add order history.')
                );
            }
            if (is_array($response)) {
                $response = Mage::helper('core')->jsonEncode($response);
                $this->getResponse()->setBody($response);
            }
            else {
                $block = $this->getLayout()->createBlock("xpos/adminhtml_override_history")
                    ->setTemplate("sm/xpos/override/history.phtml")->toHtml();

                $result = array('block'=>$block);
                $this->getResponse()
                    ->clearHeaders()
                    ->setHeader('Content-Type', 'application/json')
                    ->setBody(Mage::helper('core')->jsonEncode($result));
                return $result;
            }
        }
    }

    public function loginAction(){
        $xpos_user_username = $this->getRequest()->getParam('xpos_user_username');
        $xpos_user_password = $this->getRequest()->getParam('xpos_user_password');

        $data_cashier = Mage::getModel('xpos/user')->getCollection();

        $user_admin = Mage::getSingleton('admin/session');
        $user_admin_id = $user_admin->getUser()->getUserId();

        $data_cashier->addFieldToFilter('username',array('eq' => $xpos_user_username));
        $data_cashier->addFieldToFilter('password',array('eq' => $xpos_user_password));
        $data_cashier->addFieldToFilter('user_id',array('eq' => $user_admin_id));
        $data_cashier->addFieldToFilter('is_active',array('eq' => 1));

        foreach($data_cashier as $row){
            $cashier['xpos_user_id'] = $row->getXposUserId();
            $cashier['username'] = $row->getUsername();
            $cashier['email'] = $row->getEmail();
            $cashier['firstname'] = $row->getFirstname();
            $cashier['lastname'] = $row->getLastname();
            $cashier['type'] = $row->getType();
            Mage::register('is_user_limited',$row->getType());
            Mage::getSingleton('adminhtml/session')->setData('is_user_limited',$row->getType());
        }
        return $this->getResponse()->setBody(json_encode($cashier));
    }

    public function customerSearchAction(){
        $items = array();

        $start = $this->getRequest()->getParam('start', 1);
        $limit = $this->getRequest()->getParam('limit', 10);
        $query = $this->getRequest()->getParam('query', '');

        //$searchInstance = new SM_XPos_Model_Search_Customer;
        $searchInstance = Mage::getModel('xpos/search_customer');

        $results = $searchInstance->setStart($start)
            ->setLimit($limit)
            ->setQuery($query)
            ->load()
            ->getResults();

        $items = array_merge_recursive($items, $results);

        $totalCount = sizeof($items);

        $block = $this->getLayout()->createBlock('adminhtml/template')
            ->setTemplate('sm/xpos/index/customer/autocomplete.phtml')
            ->assign('items', $items);
        $this->getResponse()->setBody($block->toHtml());
    }

    public function currentbalanceAction($type = 'json'){
        $till_id = $this->getRequest()->getParam('till_id');
        $return = Mage::getSingleton('xpos/transaction')->currentBalance($till_id);
        if ($type == 'json')
            return $this->getResponse()->setBody(json_encode($return));
    }

    public function newTransactionAction(){
        $amount = $this->getRequest()->getParam('amount');
        $note = $this->getRequest()->getParam('note');
        $note = trim(strip_tags($note));
        $type = $this->getRequest()->getParam('type');
        $till_id = $this->getRequest()->getParam('till_id');
        $result = false;
        if (
            is_numeric($amount) &&
            $amount > 0 &&
            in_array($type, array(
                    'in',
                    'out')
            )
        ) {

            $data = array(
                'amount' => $amount,
                'type' => $type,
                'till_id' => $till_id,
                'note' => $note
            );
            $result = Mage::getModel('xpos/transaction')->saveTransaction($data);
        }

        if ($result == false) {
            $result = array(
                'msg' => 'Can NOT save this transaction. Please recheck the input value or contact with your administrator',
                'error' => true);
        }

        $this->getResponse()->setBody(json_encode($result));


    }

    public function customerLoadAction(){
        $limit = $this->getRequest()->getParam('limit', 10);
        $page = $this->getRequest()->getParam('page', 1);
        $items = array();

        //$searchInstance = new SM_XPos_Model_Search_Customer;
        $searchInstance = Mage::getModel('xpos/search_customer');
        $results = $searchInstance
            ->loadAll($limit, $page);

        if ($results)
            $items = $results->getResults();

        $this->getResponse()->setBody(json_encode($items));
    }

    public function updateQtyAction()
    {
        try {
            $creditmemo = $this->_initCreditmemo(true);
            $this->loadLayout();
            $response = $this->getLayout()->getBlock('order_items')->toHtml();
        } catch (Mage_Core_Exception $e) {
            $response = array(
                'error' => true,
                'message' => $e->getMessage()
            );
            $response = Mage::helper('core')->jsonEncode($response);
        } catch (Exception $e) {
            $response = array(
                'error' => true,
                'message' => $this->__('Cannot update the item\'s quantity.')
            );
            $response = Mage::helper('core')->jsonEncode($response);
        }
        $this->getResponse()->setBody($response);
    }

    public function getSavedOrderAction(){
        $collection = Mage::getResourceModel('sales/order_collection');
        $collection->addFieldToFilter('status', 'pending');
        $collection->addFieldToFilter('xpos', 1);
        echo $collection->getSize();die;
    }

    public function checkNetworkAction(){
        die("ok");
    }

}
