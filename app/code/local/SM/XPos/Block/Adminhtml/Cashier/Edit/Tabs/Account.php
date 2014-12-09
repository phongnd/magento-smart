<?php
/**
 * Author: Hieunt
 * Date: 4/5/13
 * Time: 9:45 AM
 */

class SM_XPos_Block_Adminhtml_Cashier_Edit_Tabs_Account extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('ocm_form', array('legend' => Mage::helper('xpos')->__('Account')));

        $fieldset->addField('username', 'text', array(
            'label' => Mage::helper('xpos')->__('Username'),
            'class'     => 'required-entry',
            'required'  => true,
            'name' => 'username',
        ));

        $fieldset->addField('password', 'password', array(
            'label' => Mage::helper('xpos')->__('Password'),
            'name' => 'required-entry',
            'required'  => true,
            'name' => 'password',
        ));

        $fieldset->addField('is_active', 'checkbox', array(
            'label'     => Mage::helper('xpos')->__('Active'),
            'onclick'   => 'this.value = this.checked ? 1 : 0;',
            'name'      => 'is_active',
        ));

        $fieldset->addField('type', 'checkbox', array(
            'label'     => Mage::helper('xpos')->__('Admin'),
            'onclick'   => 'this.value = this.checked ? 1 : 0;',
            'name'      => 'type',
        ));

        $data_admin = Mage::getModel('admin/user')->getCollection();
        $data_admin->addFieldToFilter('is_active',array('eq' => 1));

        $listAdmin = array();
        foreach($data_admin as $row){
            $listAdmin[] = array('value' => $row->getUserId() , 'label' => $row->getUsername());
        }

        $fieldset->addField('user_id', 'select', array(
            'label' => Mage::helper('xpos')->__('User Cashier'),
            'values' => $listAdmin,
            'class'     => 'required-entry',
            'required'  => true,
            'name' => 'user_id',
        ));

        if (Mage::getSingleton('adminhtml/session')->getCashierData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getCashierData());
            Mage::getSingleton('adminhtml/session')->getCashierData(null);
        } elseif (Mage::registry('cashier_data')) {
            $form->setValues(Mage::registry('cashier_data')->getData());
            $formData = Mage::registry('cashier_data')->getData();
            $form->getElement('is_active')->setIsChecked(!empty($formData['is_active']));
            $form->getElement('type')->setIsChecked(!empty($formData['type']));
        }

    }
}