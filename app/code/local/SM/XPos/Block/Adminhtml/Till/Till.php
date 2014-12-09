<?php

class SM_XPos_Block_Adminhtml_Till_Till extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_controller = 'adminhtml_till';
        $this->_blockGroup = 'xpos';
        $this->_headerText = Mage::helper('xpos')->__('Till Management');
        $this->_addButtonLabel = Mage::helper('xpos')->__('Add till');

    }
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new');
    }
}