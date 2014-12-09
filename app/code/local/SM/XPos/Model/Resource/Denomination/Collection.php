<?php
/**
 * Created by PhpStorm.
 * User: Smartor
 * Date: 10/21/14
 * Time: 3:16 PM
 */

class SM_XPos_Model_Resource_Denomination_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract{
    public function _construct(){
        parent::_construct();
        $this->_init('xpos/denomination');
    }

}
