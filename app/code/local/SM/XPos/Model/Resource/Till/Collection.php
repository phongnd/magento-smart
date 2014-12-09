<?php

class SM_XPos_Model_Resource_Till_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('xpos/till');
    }

}