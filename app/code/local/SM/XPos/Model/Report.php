<?php
/**
 * Created by PhpStorm.
 * User: Le Nam
 * Date: 10/16/14
 * Time: 10:35 AM
 */
class SM_XPos_Model_Report extends Mage_Core_Model_Abstract{
    public function _construct(){
        parent::_construct();
        $this->_init('xpos/report');
    }

    public function getType(){
        return $this->getData('type');
    }


}