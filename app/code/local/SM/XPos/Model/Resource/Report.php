<?php
class SM_XPos_Model_Resource_Report extends Mage_Core_Model_Mysql4_Abstract{
    public function _construct(){
        $this->_init('xpos/report','report_id');
    }
}