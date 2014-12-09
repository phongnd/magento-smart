<?php
class SM_XPos_Model_Sales_Create extends Mage_Adminhtml_Model_Sales_Order_Create
{

    //Overrride init from order function to update price
    public function initFromOrder(Mage_Sales_Model_Order $order)
    {
        if (!$order->getReordered()) {
            $this->getSession()->setOrderId($order->getId());
        } else {
            $this->getSession()->setReordered($order->getId());
        }

        /**
         * Check if we edit quest order
         */
        $this->getSession()->setCurrencyId($order->getOrderCurrencyCode());
        if ($order->getCustomerId()) {
            $this->getSession()->setCustomerId($order->getCustomerId());
        } else {
            $this->getSession()->setCustomerId(false);
        }

        $this->getSession()->setStoreId($order->getStoreId());

        //Notify other modules about the session quote
        Mage::dispatchEvent('init_from_order_session_quote_initialized',
            array('session_quote' => $this->getSession()));

        /**
         * Initialize catalog rule data with new session values
         */
        $this->initRuleData();
        foreach ($order->getItemsCollection(
                     array_keys(Mage::getConfig()->getNode('adminhtml/sales/order/create/available_product_types')->asArray()),
                     true
                 ) as $orderItem) {
            /* @var $orderItem Mage_Sales_Model_Order_Item */
            if (!$orderItem->getParentItem()) {
                if ($order->getReordered()) {
                    $qty = $orderItem->getQtyOrdered();
                } else {
                    $qty = $orderItem->getQtyOrdered() - $orderItem->getQtyShipped() - $orderItem->getQtyInvoiced();
                }

                if ($qty > 0) {
                    $item = $this->initFromOrderItem($orderItem, $qty);
                    if (is_string($item)) {
                        Mage::throwException($item);
                    }
                }
            }
        }

        $shippingAddress = $order->getShippingAddress();
        if ($shippingAddress) {
            $addressDiff = array_diff_assoc($shippingAddress->getData(), $order->getBillingAddress()->getData());
            unset($addressDiff['address_type'], $addressDiff['entity_id']);
            $shippingAddress->setSameAsBilling(empty($addressDiff));
        }

        $this->_initBillingAddressFromOrder($order);
        $this->_initShippingAddressFromOrder($order);

        if (!$this->getQuote()->isVirtual() && $this->getShippingAddress()->getSameAsBilling()) {
            $this->setShippingAsBilling(1);
        }

        $this->setShippingMethod($order->getShippingMethod());
        $this->getQuote()->getShippingAddress()->setShippingDescription($order->getShippingDescription());

        $this->getQuote()->getPayment()->addData($order->getPayment()->getData());


        $orderCouponCode = $order->getCouponCode();
        if ($orderCouponCode) {
            $this->getQuote()->setCouponCode($orderCouponCode);
        }

        if ($this->getQuote()->getCouponCode()) {
            $this->getQuote()->collectTotals();
        }

        Mage::helper('core')->copyFieldset(
            'sales_copy_order',
            'to_edit',
            $order,
            $this->getQuote()
        );

        Mage::dispatchEvent('sales_convert_order_to_quote', array(
            'order' => $order,
            'quote' => $this->getQuote()
        ));

        if (!$order->getCustomerId()) {
            $this->getQuote()->setCustomerIsGuest(true);
        }

        if ($this->getSession()->getUseOldShippingMethod(true)) {
            /*
             * if we are making reorder or editing old order
             * we need to show old shipping as preselected
             * so for this we need to collect shipping rates
             */
            $this->collectShippingRates();
        } else {
            /*
             * if we are creating new order then we don't need to collect
             * shipping rates before customer hit appropriate button
             */
            $this->collectRates();
        }

        // Make collect rates when user click "Get shipping methods and rates" in order creating
        // $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        // $this->getQuote()->getShippingAddress()->collectShippingRates();

        $this->getQuote()->save();
        $cur_quote = $quote = $this->getQuote()->getId();
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
                        $data = $this->processDatas($data);
                        $this->updateQuoteItemsConfig($data);
                    }
                }
            }
        }

        return $this;
    }

    //process
    protected function processDatas($items)
    {
        /* @var $productHelper Mage_Catalog_Helper_Product */
        $productHelper = Mage::helper('catalog/product');
        foreach ($items as $id => $item) {
            $buyRequest = new Varien_Object($item);
            $params = array('files_prefix' => 'item_' . $id . '_');
            $buyRequest = $productHelper->addParamsToBuyRequest($buyRequest, $params);
            if ($buyRequest->hasData()) {
                $items[$id] = $buyRequest->toArray();
            }
        }
        return $items;
    }

    //Get custom price of save order
    public function initFromOrderItem(Mage_Sales_Model_Order_Item $orderItem, $qty = null)
    {
        if (!$orderItem->getId()) {
            return $this;
        }
        $info = $orderItem->getProductOptionByCode('info_buyRequest');
        $info = new Varien_Object($info);

        $price = $info->getData('custom_price');

        $product = Mage::getModel('catalog/product')
            ->setStoreId($this->getSession()->getStoreId())
            ->load($orderItem->getProductId());

        if ($product->getId()) {
            if($price)
            $product->setData('price',$price);
            $product->setSkipCheckRequiredOption(true);
            $buyRequest = $orderItem->getBuyRequest();
            if (is_numeric($qty)) {
                $buyRequest->setQty($qty);
            }
           // $item = $this->getQuote()->addProduct($product, $buyRequest);
            $item = $this->addProductReload($product, $buyRequest);
            if (is_string($item)) {
                return $item;
            }

            if ($additionalOptions = $orderItem->getProductOptionByCode('additional_options')) {
                $item->addOption(new Varien_Object(
                    array(
                        'product' => $item->getProduct(),
                        'code' => 'additional_options',
                        'value' => serialize($additionalOptions)
                    )
                ));
            }

            Mage::dispatchEvent('sales_convert_order_item_to_quote_item', array(
                'order_item' => $orderItem,
                'quote_item' => $item
            ));
            return $item;
        }

        return $this;
    }

    public function recollectCart(){
        if ($this->_needCollectCart === true) {
            $this->getCustomerCart()
                ->collectTotals()
                ->save();
        }
        $this->setRecollect(true);
        return $this;
    }

    /**
     * Update quantity of order quote items
     *
     * @param   array $data
     * @return  SM_XPos_Model_Adminhtml_Sales_Order_Create
     */
    public function updateQuoteItems($data)
    {
        if (is_array($data)) {
            try {
                foreach ($data as $itemId => $info) {
                    if (!empty($info['configured'])) {
                        $item = $this->getQuote()->updateItem($itemId, new Varien_Object($info));
                        $itemQty = (float)$item->getQty();
                    } else {
                        $item       = $this->getQuote()->getItemById($itemId);
                        $itemQty    = (float)$info['qty'];
                    }
                    if ($item) {
                        if ($item->getProduct()->getStockItem()) {
                            if (!$item->getProduct()->getStockItem()->getIsQtyDecimal()) {
                                $itemQty = (int)$itemQty;
                            } else {
                                $item->setIsQtyDecimal(1);
                            }
                        }

                        //$itemQty    = $itemQty > 0 ? $itemQty : 1;
                        if($itemQty > 0) {
                            if (isset($info['custom_price'])) {
                                $itemPrice  = $this->_parseCustomPrice($info['custom_price']);
                            } else {
                                $itemPrice = null;
                            }
                            // $noDiscount = !isset($info['use_discount']);
                            $noDiscount = false;

                            if (empty($info['action']) || !empty($info['configured'])) {
                                $item->setQty($itemQty);
                                $item->setCustomPrice($itemPrice);
                                $item->setOriginalCustomPrice($itemPrice);
                                $item->setNoDiscount($noDiscount);
                                $item->getProduct()->setIsSuperMode(true);
                                $item->getProduct()->unsSkipCheckRequiredOption();
                                $item->checkData();
                            } else {
                                $this->moveQuoteItem($item->getId(), $info['action'], $itemQty);
                            }
                        } else {
                            $this->getQuote()->removeItem($item->getId());
                        }
                    } else {
                        try {
                            $itemQty    = (float)$info['qty'];
                            $t = explode('-',$itemId);
                            $realItemId = $itemId;
                            if (isset($t[0])){
                                $realItemId = $t[0];
                            }

                            if ($itemQty > 0 && $realItemId > 0){
                                //$reload = Mage::app()->getRequest()->getParam('reload_order');
                                $reload = 0;
                                if($reload){
                                    if (strpos($itemId, '-') === false) {
                                        $quote_item = Mage::getModel('sales/quote_item')->load($itemId);
                                        $product_id = $quote_item->getData('product_id');
                                        $this->addProductReload($product_id,$info);
                                    }
                                }
                                else{
                                    $this->addProductReload($itemId, $info);
                                }
                            }
                            /**
                             * Fixed: Cant update custom price at first time click button Update Items
                             */
                            $this->recollectCart();
                            $this->updateCustomPrice($data);
                        }
                        catch (Mage_Core_Exception $e){
                            $this->getSession()->addError($e->getMessage());
                        }
                        catch (Exception $e){
                            return $e;
                        }
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $this->recollectCart();
                throw $e;
            } catch (Exception $e) {
                Mage::logException($e);
            }
            $this->recollectCart();
        }
        return $this;
    }


    //update config product
    public function updateQuoteItemsConfig($data)
    {
        if (is_array($data)) {
            try {
                foreach ($data as $itemId => $info) {
                        $item       = $this->getQuote()->getItemById($itemId);
                        $itemQty    = (float)$info['qty'];
                    if ($item) {
                        //$itemQty    = $itemQty > 0 ? $itemQty : 1;
                        if($itemQty > 0) {
                            if (isset($info['custom_price'])) {
                                $itemPrice  = $this->_parseCustomPrice($info['custom_price']);
                            } else {
                                $itemPrice = null;
                            }
                            // $noDiscount = !isset($info['use_discount']);
                            $noDiscount = false;

                            if (empty($info['action']) || !empty($info['configured'])) {
                                $item->setQty($itemQty);
                                $item->setCustomPrice($itemPrice);
                                $item->setOriginalCustomPrice($itemPrice);
                                $item->setNoDiscount($noDiscount);
                                $item->getProduct()->setIsSuperMode(true);
                                $item->getProduct()->unsSkipCheckRequiredOption();
                                $item->checkData();

                                $this->recollectCart();
                                $item->save();
                            }
                        } else {
                            $this->getQuote()->removeItem($item->getId());
                        }
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $this->recollectCart();
                throw $e;
            } catch (Exception $e) {
                Mage::logException($e);
            }
            $this->recollectCart();
        }
        return $this;
    }

    //Update Custom price

    public function updateCustomPrice($data)
    {
        if (is_array($data)) {
            try {
                foreach ($data as $itemId => $info) {
                    if (!empty($info['configured'])) {
                        $item = $this->getQuote()->updateItem($itemId, new Varien_Object($info));
                        $itemQty = (float)$item->getQty();
                    } else {
//                        $p = Mage::getModel('catalog/product')->load($itemId);
//                        $item       = $this->getQuote()->getItemByProduct($p);
//                        Fix bug
                        $item = $this->_getItemForUpdate($itemId, $info);

                        $itemQty    = (float)$info['qty'];
                    }
                    if ($item) {
                        if ($item->getProduct()->getStockItem()) {
                            if (!$item->getProduct()->getStockItem()->getIsQtyDecimal()) {
                                $itemQty = (int)$itemQty;
                            } else {
                                $item->setIsQtyDecimal(1);
                            }
                        }

                        //$itemQty    = $itemQty > 0 ? $itemQty : 1;
                        if($itemQty > 0) {
                            if (isset($info['custom_price'])) {
                                $itemPrice  = $this->_parseCustomPrice(floatval(preg_replace('/[^\d\.]/', '', $info['custom_price'])));
                            } else {
                                $itemPrice = null;
                            }
                            //HiepHM Fixed bug Can not add Coupon code 9/4/2013
                            // $noDiscount = !isset($info['use_discount']);
                            $noDiscount = false;

                            if (empty($info['action']) || !empty($info['configured'])) {
                                $item->setQty($itemQty);
                                $item->setCustomPrice($itemPrice);
                                $item->setOriginalCustomPrice($itemPrice);
                                $item->setNoDiscount($noDiscount);
                                $item->getProduct()->setIsSuperMode(true);
                                $item->getProduct()->unsSkipCheckRequiredOption();
                                $item->checkData();
                            } else {
                                $this->moveQuoteItem($item->getId(), $info['action'], $itemQty);
                            }
                        }
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $this->recollectCart();
                throw $e;
            } catch (Exception $e) {
                Mage::logException($e);
            }
            $this->recollectCart();
        }
        return $this;
    }

    private function _getItemForUpdate($itemId, $info)
    {
        $product = Mage::getModel('catalog/product')->load($itemId);
        // Get item
        foreach ($this->getQuote()->getAllItems() as $item) {
            $itemProduct = $item->getProduct();
            if (!$product || $itemProduct->getId() != $product->getId()) {
                continue;
            }
            /**
             * Check maybe product is planned to be a child of some quote item - in this case we limit search
             * only within same parent item
             */
            $stickWithinParent = $product->getStickWithinParent();
            if ($stickWithinParent) {
                if ($item->getParentItem() !== $stickWithinParent) {
                    continue;
                }
            }
            $optionsByCode = $item->getOptionsByCode();
            $optionsCompare = unserialize($optionsByCode['info_buyRequest']->getValue());
            foreach($optionsCompare as $key => $value) {
                if ($value != $info[$key]) {
                    continue;
                }
            }
            return $item;
        }
        return false;
    }

    public function createOrder()
    {
        $this->_prepareCustomer();
        $this->_errors = array_unique($this->_errors);

        $quote = $this->getQuote();
        // hieunt : fixed get shiping
        $shippingAddress = $quote->getShippingAddress();
        $shippingMethod = Mage::registry('pos_shipping_method');

        if ($shippingMethod) {
            $shippingAddress->setCollectShippingRates(true)->collectShippingRates()
                ->setShippingMethod($shippingMethod);
        }
        $this->_validate();

        $this->_prepareQuoteItems();

        if (! $quote->getCustomer()->getId() || ! $quote->getCustomer()->isInStore($this->getSession()->getStore())) {
            $account = $this->getData('account');
            if($account['type'] != 'guest'){
                $quote->getCustomer()->sendNewAccountEmail('registered', '', $quote->getStoreId());
            }
        }
        $service = Mage::getModel('xpos/sales_quote', $quote);
        if ($this->getSession()->getOrder()->getId()) {
            $oldOrder = $this->getSession()->getOrder();
            $originalId = $oldOrder->getOriginalIncrementId();
            if (!$originalId) {
                $originalId = $oldOrder->getIncrementId();
            }
            $orderData = array(
                'original_increment_id'     => $originalId,
                'relation_parent_id'        => $oldOrder->getId(),
                'relation_parent_real_id'   => $oldOrder->getIncrementId(),
                'edit_increment'            => $oldOrder->getEditIncrement()+1,
                'increment_id'              => $originalId.'-'.($oldOrder->getEditIncrement()+1)
            );
            $quote->setReservedOrderId($orderData['increment_id']);
            $service->setOrderData($orderData);
        }

        $order = $service->submit();

        if (!$quote->getCustomer()->getId() || !$quote->getCustomer()->isInStore($this->getSession()->getStore())) {
            $quote->getCustomer()->setCreatedAt($order->getCreatedAt());

            $quote->getCustomer()->save();
        }
        if ($this->getSession()->getOrder()->getId()) {
            $oldOrder = $this->getSession()->getOrder();

            $this->getSession()->getOrder()->setRelationChildId($order->getId());
            $this->getSession()->getOrder()->setRelationChildRealId($order->getIncrementId());
            $this->getSession()->getOrder()->cancel()
                ->save();
            $order->save();
        }
        if ($this->getSendConfirmation()) {
            $order->sendNewOrderEmail();
        }

        Mage::dispatchEvent('checkout_submit_all_after', array('order' => $order, 'quote' => $quote));

        return $order;
    }

    public function getCustomerGroupId()
    {
        $orderXpos = Mage::registry('xpos_order');
        if (isset($orderXpos))
            $groupId = $orderXpos;
        else
            $groupId = $this->getQuote()->getCustomerGroupId();
        if (!$groupId) {
            $groupId = $this->getSession()->getCustomerGroupId();
        }
        return $groupId;
    }

    public function addProduct($product, $config = 1)
    {
        if (!is_array($config) && !($config instanceof Varien_Object)) {
            $config = array('qty' => $config);
        }
        $config = new Varien_Object($config);

        if (!($product instanceof Mage_Catalog_Model_Product)) {
            $productId = $product;
            $product = Mage::getModel('catalog/product')
                ->setStore($this->getSession()->getStore())
                ->setStoreId($this->getSession()->getStoreId())
                ->load($product);
            if (!$product->getId()) {
                Mage::throwException(
                    Mage::helper('adminhtml')->__('Failed to add a product to cart by id "%s".', $productId)
                );
            }
        }

        $stockItem = $product->getStockItem();
        if ($stockItem && $stockItem->getIsQtyDecimal()) {
            $product->setIsQtyDecimal(1);
        } else {
            $config->setQty((int) $config->getQty());
        }

        $product->setCartQty($config->getQty());
        $item = $this->getQuote()->addProductAdvanced(
            $product,
            $config,
            Mage_Catalog_Model_Product_Type_Abstract::PROCESS_MODE_FULL
        );
        if (is_string($item)) {
            $item = $this->getQuote()->addProductAdvanced(
                $product,
                $config,
                Mage_Catalog_Model_Product_Type_Abstract::PROCESS_MODE_LITE
            );
            if (is_string($item)) {
                Mage::throwException($item);
            }
        }
        $item->checkData();

        $this->setRecollect(true);
        return $this;
    }

    public function addProductReload($product, $config = 1)
    {
        if (!is_array($config) && !($config instanceof Varien_Object)) {
            $config = array('qty' => $config);
        }
        $config = new Varien_Object($config);

        if (!($product instanceof Mage_Catalog_Model_Product)) {
            $productId = $product;
            $product = Mage::getModel('catalog/product')
                ->setStore($this->getSession()->getStore())
                ->setStoreId($this->getSession()->getStoreId())
                ->load($product);
            if (!$product->getId()) {
                Mage::throwException(
                    Mage::helper('adminhtml')->__('Failed to add a product to cart by id "%s".', $productId)
                );
            }
        }

        $stockItem = $product->getStockItem();
        if ($stockItem && $stockItem->getIsQtyDecimal()) {
            $product->setIsQtyDecimal(1);
        } else {
            $config->setQty((int) $config->getQty());
        }

        $product->setCartQty($config->getQty());
        $item = $this->getQuote()->addProductAdvanced(
            $product,
            $config,
            Mage_Catalog_Model_Product_Type_Abstract::PROCESS_MODE_FULL
        );
        if (is_string($item)) {
            $item = $this->getQuote()->addProductAdvanced(
                $product,
                $config,
                Mage_Catalog_Model_Product_Type_Abstract::PROCESS_MODE_LITE
            );
            if (is_string($item)) {
                Mage::throwException($item);
            }
        }


        if (isset($config['custom_price'])) {
            $itemPrice  = $this->_parseCustomPrice($config['custom_price']);
        }

        //XPOS-1447: If grouped product then do not process further since their children already be added in addProductAdvanced()
        if(isset($config['super_group'])) {
            return $this;
        }

        $itemQty    = (float)$config['qty'];
        if (empty($config['action']) || !empty($config['configured'])) {
            $item->setQty($itemQty);
            $item->setCustomPrice($itemPrice);
            $item->setOriginalCustomPrice($itemPrice);
            $item->getProduct()->setIsSuperMode(true);
            $item->getProduct()->unsSkipCheckRequiredOption();
            //$item->checkData();
        }
        $item->checkData();

        $this->setRecollect(true);
        return $this;
    }

}
