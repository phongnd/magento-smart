<?php

class SM_XPos_Block_Adminhtml_Cashier_Cashier extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
      parent::__construct();
        $this->_controller = 'adminhtml_cashier';
        $this->_blockGroup = 'xpos';
        $this->_headerText = Mage::helper('xpos')->__('Cashier Manager');
        $this->_addButtonLabel = Mage::helper('xpos')->__('Add cashier');

  }
}