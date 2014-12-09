<?php

class SM_XPos_Model_Resource_Transaction extends Mage_Core_Model_Mysql4_Abstract{
    public function _construct(){
        $this->_init('xpos/transaction','transaction_id');
    }
}