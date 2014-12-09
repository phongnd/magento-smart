<?php
/**
 * Created by PhpStorm.
 * User: Le Nam
 * Date: 10/16/14
 * Time: 11:22 AM
 */
class SM_XPos_Model_Resource_Report_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract{
    public function _construct(){
        parent::_construct();
        $this->_init('xpos/report');
    }

}
