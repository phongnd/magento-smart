<?php

class SM_XPos_Model_Resource_User extends Mage_Core_Model_Mysql4_Abstract{
    public function _construct(){
        $this->_init('xpos/user','xpos_user_id');
    }
}