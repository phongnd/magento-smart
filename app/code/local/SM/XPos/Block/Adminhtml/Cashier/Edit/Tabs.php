<?php

class SM_XPos_Block_Adminhtml_Cashier_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('cashier_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('xpos')->__('Cashier'));
    }

    protected function _beforeToHtml() {
        $this->addTab('account_section', array(
            'label' => $this->__('Account'),
            'title' => $this->__('Account'),
            'content' => $this->getLayout()->createBlock('xpos/adminhtml_cashier_edit_tabs_account')->toHtml(),
        ));

        $this->addTab('general_section', array(
            'label' => $this->__('General Information'),
            'title' => $this->__('General Information'),
            'content' => $this->getLayout()->createBlock('xpos/adminhtml_cashier_edit_tabs_form')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }

}