<?php

class SM_XPos_Block_Adminhtml_Till_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Add jQuery
    */
    public function _prepareLayout() {
        parent::_prepareLayout();
    }

    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'xpos';
        $this->_controller = 'adminhtml_till';
        
        $this->_updateButton('save', 'label', Mage::helper('xpos')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('xpos')->__('Delete'));
        $this->_removeButton('reset');
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('till_data') && Mage::registry('till_data')->getTillId() ) {
            return Mage::helper('xpos')->__("Edit Till '%s'", $this->htmlEscape(Mage::registry('till_data')->getUsername()));
        } else {
            return Mage::helper('xpos')->__('Add Till');
        }
    }
    public function getBackUrl()
    {
        return $this->getUrl('*/*');
    }

    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', array($this->_objectId => $this->getRequest()->getParam($this->_objectId)));
    }
}
