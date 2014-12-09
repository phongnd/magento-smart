<?php

class SM_XPos_Block_Adminhtml_Cashier_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Add jQuery
    */
    public function _prepareLayout() {
        /*$layout = $this->getLayout();
        Mage::helper('xpos')->addJQuery($layout);*/
        parent::_prepareLayout();
    }

    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'xpos';
        $this->_controller = 'adminhtml_cashier';
        
        $this->_updateButton('save', 'label', Mage::helper('xpos')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('xpos')->__('Delete'));
		
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
        if( Mage::registry('cashier_data') && Mage::registry('cashier_data')->getXposUserId() ) {
            return Mage::helper('xpos')->__("Edit cashier '%s'", $this->htmlEscape(Mage::registry('cashier_data')->getUsername()));
        } else {
            return Mage::helper('xpos')->__('Add cashier');
        }
    }
}