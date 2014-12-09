<?php
/**
 * Created by PhpStorm.
 * User: Smartor
 * Date: 10/21/14
 * Time: 3:20 PM
 */
class SM_XPos_Model_Resource_Denomination extends Mage_Core_Model_Mysql4_Abstract{
    public function _construct(){
        $this->_init('xpos/denomination','deno_id');
    }
}