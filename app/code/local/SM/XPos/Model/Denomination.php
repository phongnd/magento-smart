<?php
class SM_XPos_Model_Denomination extends Mage_Core_Model_Abstract{
    public function _construct(){
    parent::_construct();
    $this->_init('xpos/denomination');
    }

    public function getType(){
        return $this->getData('type');
    }

    public function toOptionArray()
    {
        return array(
            array('value'=>0, 'label'=>Mage::helper('xpos')->__('Use Custom Value')),
            array('value'=>1, 'label'=>Mage::helper('xpos')->__('USD (United States Dollars)')),
            array('value'=>2, 'label'=>Mage::helper('xpos')->__('AUD (Australian Dollars)')),
            array('value'=>3, 'label'=>Mage::helper('xpos')->__('EUR (Euro)')),
            array('value'=>4, 'label'=>Mage::helper('xpos')->__('GBP (British Pounds)')),
        );
    }


}