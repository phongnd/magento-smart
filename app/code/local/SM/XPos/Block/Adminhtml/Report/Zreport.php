<?php
/**
 * Created by PhpStorm.
 * User: Smartor
 * Date: 10/15/14
 * Time: 9:39 AM
 */

class SM_XPos_Block_Adminhtml_Report_Zreport extends Mage_Adminhtml_Block_Template {

    public function __construct(){
        $this->setTemplate('sm/xpos/report/zreport.phtml');
        parent::_construct();
    }

    public function getTillId(){
        $till_id = $this->getRequest()->getParam('till_id');
        Mage::getSingleton('adminhtml/session')->setTillInfo($till_id);
        return $till_id;
    }
    public function getTillName(){
        $till_id = $this->getRequest()->getParam('till_id');
        $tillName = Mage::getModel('xpos/till')->load($till_id)->getData('till_name');
        Mage::getSingleton('adminhtml/session')->setTillInfo($till_id);
        return $tillName;
    }

    public function getPaymentPaidInfo(){
        $data = array();

        if(Mage::getStoreConfig('xpos/general/enable_till') == 1){
            $till_id = $this->getRequest()->getParam('till_id');
        }
        else{
            $till_id = 'NULL';
        }

        if(Mage::getStoreConfig('xpos/general/integrate_xmwh_enabled')){
            $warehouse_id = Mage::getSingleton('admin/session')->getWarehouseId();
            Mage::getSingleton('adminhtml/session')->setWarehouseReport($warehouse_id);
        }
        else $warehouse_id = 'NULL';

        if($till_id == 'NULL')
        $report_collec = Mage::getModel('xpos/report')->getCollection()
                        ->addFieldToFilter('till_id',array('eq'=>0))
                        ->addOrder('report_id','DESC');
        else
            $report_collec = Mage::getModel('xpos/report')->getCollection()
                ->addFieldToFilter('till_id',array('eq'=>$till_id))
                ->addOrder('report_id','DESC');

        if(count($report_collec)>0){
            $fist_item = $report_collec->getFirstItem();
            $previous_time = $fist_item->getData('created_time');
        }
        else $previous_time = '2014-01-01 01:01:01';
        if($till_id != 'NULL')
        $collection = Mage::getModel('sales/order')->getCollection()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('created_at', array('from'=>$previous_time))
                    ->addAttributeToFilter('till_id',array('eq'=>$till_id))
                    ->load();
        else
            $collection = Mage::getModel('sales/order')->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('created_at', array('from'=>$previous_time))
                ->load();

        $data['other_payment']['num_order_total'] = count($collection);
        $data['other_payment']['previous_time'] = $previous_time;
        $data['other_payment']['grand_order_total'] = 0;
        $data['other_payment']['tax_order_total'] = 0;
        $data['other_payment']['total_refund'] = 0;
        $payment_arr = array();
        $data['other_payment']['money_system'] = 0;
        $data['other_payment']['order_count'] = 0;
        $data['other_payment']['transac_in'] = 0;
        $data['other_payment']['transac_out'] = 0;

        //Get transaction info of day
        $default_transfer = Mage::getStoreConfig('xpos/report/default_transfer');
        //getReal Current balance of till
        if($till_id == 'NULL'){
            $transacs = Mage::getModel('xpos/transaction')->getCollection()
                ->addFieldToFilter('till_id',array('eq'=>0))
                ->addFieldToFilter('transac_flag',array('eq'=>'1'))
                ->addFieldToFilter('created_time', array('from'=>$previous_time))
                ->addOrder('transaction_id','DESC');
        }
        else{
            $transacs = Mage::getModel('xpos/transaction')->getCollection()
                ->addFieldToFilter('till_id',array('eq'=>$till_id))
                ->addFieldToFilter('transac_flag',array('eq'=>'1'))
                ->addFieldToFilter('created_time', array('from'=>$previous_time))
                ->addOrder('transaction_id','DESC');
        }

        $real_current_balance =  $transacs->getFirstItem()->getData('current_balance');
        $previous_transfer = $transacs->getLastItem()->getData('current_balance');

        if($till_id == 'NULL'){
            $transac_collection = Mage::getModel('xpos/transaction')->getCollection()
                ->addFieldToFilter('till_id',array('eq'=>0))
                ->addFieldToFilter('order_id',array('eq'=>'Manual'))
                ->addFieldToFilter('transac_flag',array('eq'=>'1'))
                ->addFieldToFilter('created_time', array('from'=>$previous_time))
                ->addOrder('transaction_id','ASC');
        }
        else{
            $transac_collection = Mage::getModel('xpos/transaction')->getCollection()
                ->addFieldToFilter('till_id',array('eq'=>$till_id))
                ->addFieldToFilter('order_id',array('eq'=>'Manual'))
                ->addFieldToFilter('transac_flag',array('eq'=>'1'))
                ->addFieldToFilter('created_time', array('from'=>$previous_time))
                ->addOrder('transaction_id','ASC');
        }

        $current_balance = $transac_collection->getFirstItem()->getData('current_balance');

        foreach($transac_collection as $transaction){
            if($transaction->getData('type') == 'in'){
                $data['other_payment']['transac_in'] += floatval($transaction->getData('cash_in'));
            }
            else{
                $data['other_payment']['transac_out'] += floatval($transaction->getData('cash_out'));
            }
        }

        if($previous_time != '2014-01-01 01:01:01')
            $amount_diff = $data['other_payment']['transac_in'] - $data['other_payment']['transac_out'] - $previous_transfer ;
        else{
            $amount_diff = $data['other_payment']['transac_in'] - $data['other_payment']['transac_out'];
            $previous_transfer =0;
        }

        // caculate Amount
        if(count($collection) >0)
        foreach($collection as $order){
            $data['other_payment']['grand_order_total'] += floatval($order->getData('base_grand_total'));
            $data['other_payment']['tax_order_total'] += floatval($order->getData('base_tax_amount'));
            if($order->getData('xpos_app_order_id') != NULL)
                $payment = 'ipadorder';
            else
                $payment = $order->getPayment()->getMethod();

            if(in_array($payment,$payment_arr)){

                $data[$payment]['money_system'] += floatval($order->getData('base_grand_total'));
                $data[$payment]['order_count'] ++;
            }
            else{
                if($payment == 'checkmo' || $payment == 'cashpayment' || $payment == 'ccsave'){
                    switch($payment){
                        case 'checkmo' :
                            $data[$payment]['payment_name'] = 'Checks' ;
                            break;
                        case 'cashpayment' :
                            $data[$payment]['payment_name'] = 'Cash' ;
                            break;
                        case 'ccsave' :
                            $data[$payment]['payment_name'] = 'Credit Card' ;
                        default:
                            break;
                    }

                    array_push($payment_arr,$payment);
                    $data[$payment]['money_system'] = floatval($order->getData('base_grand_total'));
                    $data[$payment]['order_count'] =1;
                }
                else{
                    $data['other_payment']['payment_name'] = 'Another Payment';
                    $data['other_payment']['money_system'] += floatval($order->getData('base_grand_total'));
                    $data['other_payment']['order_count'] ++;
                }

            }

        }

        //calculate refund amount base on order update time
        if($till_id != 'NULL')
            $refundCollection = Mage::getModel('sales/order')->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('updated_at', array('from'=>$previous_time))
                ->addAttributeToFilter('till_id',array('eq'=>$till_id))
                ->load();
        else
            $refundCollection = Mage::getModel('sales/order')->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('updated_at', array('from'=>$previous_time))
                ->load();

        if(count($refundCollection) > 0){
            foreach($refundCollection as $order){
                $data['other_payment']['total_refund'] += floatval($order->getData('base_total_refunded'));
            }
        }

        //Count cash(transfer info)
            $total = $current_balance + $data['cashpayment']['money_system'];
            $result = $total + $amount_diff;
            $data['cashpayment']['payment_name'] = 'Cash' ;
            $data['cashpayment']['money_system'] = $result;
            $data['cashpayment']['total'] = $total;
            $data['cashpayment']['in_out'] = $amount_diff;
            $data['cashpayment']['previous_transfer'] = $previous_transfer;

            $data['other_payment']['grand_order_total'] += $previous_transfer + $amount_diff;

            $data['other_payment']['till_current'] = $real_current_balance;

        Mage::getSingleton('adminhtml/session')->setPaymentInfo($data);
        return $data;
    }

    public function getDiscountPaidInfo(){
        $report_collec = Mage::getModel('xpos/report')->getCollection()
            ->addOrder('report_id','DESC');
        if(count($report_collec)>0){
            $fist_item = $report_collec->getFirstItem();
            $previous_time = $fist_item->getData('created_time');
        }
        else $previous_time = '2014-01-01 01:01:01';

        $data = array();
        $data['discount_amount'] =0;
        $data['order_count'] =0;
        $data['voucher'] = 0;
        $data['voucher_orders'] = 0;

        if(Mage::getStoreConfig('xpos/general/enable_till') == 1){
            $till_id = $this->getRequest()->getParam('till_id');
        }
        else{
            $till_id = 'NULL';
        }

        if($till_id == 'NULL')
        $collection = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('created_at', array('from'=>$previous_time))
            ->load();
        else
            $collection = Mage::getModel('sales/order')->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('till_id',array('eq'=>$till_id))
                ->addAttributeToFilter('created_at', array('from'=>$previous_time))
                ->load();

        if(count($collection) >0)
        foreach($collection as $order){
            if( Mage::getEdition()=="Enterprise"){
                if($order->getData('base_customer_balance') != NULL || $order->getData('base_gift_cards_amount')!= NULL)
                    $data['voucher_orders']++;
                $data['voucher'] += floatval($order->getData('base_customer_balance_amount'));
                $data['voucher'] += floatval($order->getData('base_gift_cards_amount'));
            }
            if($order->getData('coupon_code') != NULL){
                $data['discount_amount'] += floatval($order->getData('base_discount_amount'));
                $data['order_count'] ++;
            }
        }

        Mage::getSingleton('adminhtml/session')->setDiscountInfo($data);
        return $data;
    }


}