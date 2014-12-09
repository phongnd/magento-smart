<?php

class SM_XPos_Model_Transaction extends Mage_Core_Model_Abstract{
    public function _construct(){
        parent::_construct();
        $this->_init('xpos/transaction');
    }

    public function getType(){
        return $this->getData('type');
    }

    public function getOrderId(){
        return $this->getData('order_id');
    }

    public function currentBalance($till_id){
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $catalogResource = Mage::getModel('catalog/product')->getResource();

        //NamLX calculator by till
        if(!$till_id){
            $till_id = Mage::getSingleton('adminhtml/session')->getTillId();
        }
        if($till_id != NULL && $till_id != ''){
            $current_balance = $readConnection->fetchOne('SELECT current_balance FROM '.$catalogResource->getTable('sm_transaction').' WHERE till_id = '.$till_id.' ORDER BY transaction_id DESC LIMIT 1');
        }
        else
            $current_balance = $readConnection->fetchOne('SELECT current_balance FROM '.$catalogResource->getTable('sm_transaction').' WHERE 1 ORDER BY transaction_id DESC LIMIT 1');
        /*
        if($till_id != NULL && $till_id != ''){
            $collection = Mage::getSingleton('xpos/transaction')->getCollection()
                        ->addFieldToFilter('till_id', array('eq' => $till_id))
                        ->addFieldToFilter('comment', array('neq' => 'Out payment'))
                        ->load();
            $cash_in = 0;
            $cash_out = 0;
            foreach($collection as $row){
                $cash_in += $row->getData('cash_in');
                $cash_out += $row->getData('cash_out');
            }
            $current_balance  = $cash_in - $cash_out;
        }
        else
            $current_balance = $readConnection->fetchOne('SELECT current_balance FROM '.$catalogResource->getTable('sm_transaction').' WHERE 1 ORDER BY transaction_id DESC LIMIT 1');
        */
        //$current_balance = $readConnection->fetchOne('SELECT current_balance FROM '.$catalogResource->getTable('sm_transaction').' WHERE 1 ORDER BY transaction_id DESC LIMIT 1');
        $current_balance = Mage::helper('core')->currency($current_balance, true, false);
        $return = array();
        $return['msg'] = $current_balance;
        return $return;
    }

    /*
     * Save transaction
     */
    public function saveTransaction($data){

        $return = array(
            'msg'=>'Error! Please recheck the form OR contact administrator for more details.',
            'error'=>true);

        $user_id = 0;
        if(Mage::getSingleton('admin/session')->getUser()){
            $user_id = Mage::getSingleton('admin/session')->getUser()->getId();
        }

        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');
        $tableName = $resource->getTableName('sm_transaction');
        $current_balance = "SELECT `current_balance` FROM " . $tableName . " WHERE 1 ORDER BY transaction_id DESC LIMIT 1";
        $current_balance = $readConnection->fetchOne($current_balance);

        $previous_balance = "SELECT `previous_balance` FROM " . $tableName . " WHERE 1 ORDER BY transaction_id DESC LIMIT 1";
        $previous_balance = $readConnection->fetchOne($previous_balance);

        $now = date('Y-m-d H:i:s');
            switch($data['type']){
                case 'in':
                    $previous_balance = $current_balance;
                    $current_balance += $data['amount'];
                    $query = 'INSERT INTO '. $tableName  . " (`cash_in`,`type`,`created_time`, `order_id`, `previous_balance`, `current_balance`, `user_id`, `xpos_user_id`, `payment_method`, `comment`, `warehouse_id`, `till_id`, `transac_flag`) VALUE ('" . $data['amount'] . "', 'in','" . $now . "', 'Manual','" .$previous_balance. "','".$current_balance."', '".$user_id."', '".$data['xpos_user_id']."', 'cash_in', '".$data['note']."' , '".$data['warehouse_id']."' , '".$data['till_id']."' , '1' )";
                    if($writeConnection->query($query)){
                        $return['msg'] = 'Transaction saved';
                        $return['error'] = false;
                    } else{
                        $return['msg'] = 'Can NOT save this transaction';
                        $return['error'] = true;
                    }
                    break;

                case 'out':

                    if($data['type'] =='out' && $current_balance >= $data['amount']){
                        $previous_balance = $current_balance;
                        $current_balance -= $data['amount'];
                        $query = 'INSERT INTO '. $tableName  . " (`cash_out`,`type`,`created_time`, `order_id`, `previous_balance`, `current_balance`,`user_id`, `xpos_user_id`, `payment_method`, `comment`, `warehouse_id`, `till_id`, `transac_flag` ) VALUE ('" . $data['amount'] . "', 'out','" . $now . "', 'Manual','" .$previous_balance. "','".$current_balance."', '".$user_id."', '".$data['xpos_user_id']."' ,'cash_out', '".$data['note']."' , '".$data['warehouse_id']."' , '".$data['till_id']."' ,'1')";
                        if($writeConnection->query($query)){
                            $return['msg'] = 'Transaction saved';
                            $return['error'] = false;
                        } else{
                            $return['msg'] = 'Can NOT save this transaction';
                            $return['error'] = true;
                        }
                    } else{
                        $return['msg'] = 'You can NOT withdraw an amount of money which is greater than the Current Balance';
                        $return['error'] = true;
                    }

                    break;

                case 'partially':

                    //Save Cash in Transaction
                    $amount = $data['partially_payment_amount_checkmo'];
                    if($amount > 0){
                        $previous_balance = $current_balance;
                        $current_balance += $amount;
                        $query = 'INSERT INTO '. $tableName  . " (`cash_in`,`cash_out`,`type`,`created_time`, `order_id`, `previous_balance`, `current_balance`,`user_id`,`xpos_user_id`, `payment_method`, `warehouse_id`, `till_id`, `transac_flag`) VALUE ('" . $data['cash_in'] . "', '".$data['cash_out']."', 'in','" . $now . "', '".$data['order_id']."','" .$previous_balance. "','".$current_balance."' , '".$user_id."', '".$data['xpos_user_id']."', 'partially' , '".$data['warehouse_id']."' , '".$data['till_id']."', '1' )";
                        if($writeConnection->query($query)){
                            $return['msg'] = 'Transaction saved';
                            $return['error'] = false;
                        } else{
                            $return['msg'] = 'Can NOT save this transaction';
                            $return['error'] = true;
                        }
                    }

                    break;

                default:
                    $amount = $data['cash_in'] - $data['cash_out'];
                    $note = "Out payment";
                    switch($data['payment_method']){
                        case "Check Money Order":
                            $note = "";
                            $previous_balance = $current_balance;
                            $current_balance += $amount;
                            break;
                        case "X-Pos Cash":
                            $previous_balance = $current_balance;
                            $current_balance += $amount;
                            $note = "";
                            break;
                    }
                    $query = 'INSERT INTO '. $tableName  . " (`cash_in`,`cash_out`,`type`,`created_time`, `order_id`, `previous_balance`, `current_balance`,`user_id`,`xpos_user_id`, `payment_method`, `comment`, `warehouse_id`, `till_id`, `transac_flag`) VALUE ('" . $data['cash_in'] . "', '".$data['cash_out']."', 'in','" . $now . "', '".$data['order_id']."','" .$previous_balance. "','".$current_balance."' , '".$user_id."', '".$data['xpos_user_id']."', '".$data['payment_method']."','".$note."', '".$data['warehouse_id']."', '".$data['till_id']."', '1' )";
                    if($writeConnection->query($query)){
                        $return['msg'] = 'Transaction saved';
                        $return['error'] = false;
                    } else{
                        $return['msg'] = 'Can NOT save this transaction';
                        $return['error'] = true;
                    }

                    break;


                    //paypaluk_direct ..
                    /*if(is_numeric($data['cash_out']) && $data['cash_out'] > 0){
                        $amount = $data['cash_out'];
                        if($amount > 0){
                            $previous_balance = $current_balance;
                            $current_balance -= $amount;
                            $query = 'INSERT INTO '. $tableName  . " (`cash_out`,`type`,`created_time`, `order_id`, `previous_balance`, `current_balance`, `user_id`, `payment_method`) VALUE ('".$data['cash_out']."', 'in','" . $now . "', '".$data['order_id']."','" .$previous_balance. "','".$current_balance."' ,'".$user_id."' , 'cybersource')";
                            if($writeConnection->query($query)){
                                $return['msg'] = 'Transaction saved';
                                $return['error'] = false;
                            } else{
                                $return['msg'] = 'Can NOT save this transaction';
                                $return['error'] = true;
                            }
                        }
                    }*/

                    break;
            }



       return $return;

    }


    /**
     * Read report
     */

    public  function reportTransaction($data){
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $tableName = 'sm_pos_transaction';
        $query = 'SELECT * FROM ';
        $results = $readConnection->fetchAll($query);

    }

}