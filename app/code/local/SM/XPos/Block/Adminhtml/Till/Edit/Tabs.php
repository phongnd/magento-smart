<?php

class SM_XPos_Block_Adminhtml_Till_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('till_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('xpos')->__('Till management'));
    }

    protected function _beforeToHtml() {
        $this->addTab('till_section', array(
            'label' => $this->__('Till'),
            'title' => $this->__('Till'),
            'content' => $this->getLayout()->createBlock('xpos/adminhtml_till_edit_tabs_till')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }

}