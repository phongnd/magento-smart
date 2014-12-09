<?php

class SM_XPos_Model_User extends Mage_Core_Model_Abstract{
    public function _construct(){
        parent::_construct();
        $this->_init('xpos/user');
    }

    public function getType(){
        return $this->getData('type');
    }

    public function getListUser(){
        $lstUser =array();
        foreach($this->getCollection() as $user)
        {
            $lstUser[$user->getData('till_id')] = $user->getData('firstname').' '. $user->getData('lastname');
        }
        return $lstUser;
    }
    public function getOrderId(){
        return $this->getData('order_id');
    }

    public function currentBalance(){
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $current_balance = $readConnection->fetchOne('SELECT current_balance FROM sm_transaction WHERE 1 ORDER BY transaction_id DESC LIMIT 1');
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

        $user_id = Mage::getSingleton('admin/session')->getUser()->getId();

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
                    $query = 'INSERT INTO '. $tableName  . " (`cash_in`,`type`,`created_time`, `order_id`, `previous_balance`, `current_balance`, `user_id`, `payment_method`, `comment`) VALUE ('" . $data['amount'] . "', 'in','" . $now . "', 'Manual','" .$previous_balance. "','".$current_balance."', '".$user_id."', 'cash_in', '".$data['note']."' )";
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
                        $query = 'INSERT INTO '. $tableName  . " (`cash_out`,`type`,`created_time`, `order_id`, `previous_balance`, `current_balance`,`user_id`, `payment_method`, `comment` ) VALUE ('" . $data['amount'] . "', 'out','" . $now . "', 'Manual','" .$previous_balance. "','".$current_balance."', '".$user_id."' ,'cash_out', '".$data['note']."' )";
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

                case 'checkmo': //Check money order

                    $amount = $data['cash_in'] - $data['cash_out'];
                    $previous_balance = $current_balance;
                    $current_balance += $amount;
                    $query = 'INSERT INTO '. $tableName  . " (`cash_in`,`cash_out`,`type`,`created_time`, `order_id`, `previous_balance`, `current_balance`,`user_id`, `payment_method`) VALUE ('" . $data['cash_in'] . "', '".$data['cash_out']."', 'in','" . $now . "', '".$data['order_id']."','" .$previous_balance. "','".$current_balance."' , '".$user_id."', '".$data['payment_method']."' )";
                    if($writeConnection->query($query)){
                        $return['msg'] = 'Transaction saved';
                        $return['error'] = false;
                    } else{
                        $return['msg'] = 'Can NOT save this transaction';
                        $return['error'] = true;
                    }

                    break;


                case 'partially':

                    //Save Cash in Transaction
                    $amount = $data['partially_payment_amount_checkmo'];
                    if($amount > 0){
                        $previous_balance = $current_balance;
                        $current_balance += $amount;
                        $query = 'INSERT INTO '. $tableName  . " (`cash_in`,`cash_out`,`type`,`created_time`, `order_id`, `previous_balance`, `current_balance`,`user_id`, `payment_method`) VALUE ('" . $data['cash_in'] . "', '".$data['cash_out']."', 'in','" . $now . "', '".$data['order_id']."','" .$previous_balance. "','".$current_balance."' , '".$user_id."', 'partially' )";
                        if($writeConnection->query($query)){
                            $return['msg'] = 'Transaction saved';
                            $return['error'] = false;
                        } else{
                            $return['msg'] = 'Can NOT save this transaction';
                            $return['error'] = true;
                        }
                    }

                    break;


                default: //paypaluk_direct ..
                    if(is_numeric($data['cash_out']) && $data['cash_out'] > 0){
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
                    }

                    break;

            }



       return $return;

    }

    /*
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