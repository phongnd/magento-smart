<?php

class SM_XPos_Block_Adminhtml_Cashier_Edit_Tabs_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        // initial form
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('ocm_form', array('legend' => Mage::helper('xpos')->__('Cashier information')));

        $fieldset->addField('firstname', 'text', array(
            'label' => Mage::helper('xpos')->__('First Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'firstname',
        ));

        $fieldset->addField('lastname', 'text', array(
            'label' => Mage::helper('xpos')->__('Last Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'lastname',
        ));

        $fieldset->addField('email', 'text', array(
            'label' => Mage::helper('xpos')->__('Email'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'email',
        ));

        if (Mage::getSingleton('adminhtml/session')->getCashierData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getCashierData());
            Mage::getSingleton('adminhtml/session')->getCashierData(null);
        } elseif (Mage::registry('cashier_data')) {
            $form->setValues(Mage::registry('cashier_data')->getData());
        }

        return parent::_prepareForm();
    }

}