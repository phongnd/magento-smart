<?php
class SM_XPos_Block_Adminhtml_Till_Edit_Tabs_Till extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('ocm_form', array('legend' => Mage::helper('xpos')->__('Till')));

        $fieldset->addField('till_name', 'text', array(
            'label' => Mage::helper('xpos')->__('Till name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name' => 'till_name',
        ));

        $fieldset->addField('is_active', 'checkbox', array(
            'label'     => Mage::helper('xpos')->__('Active'),
            'onclick'   => 'this.value = this.checked ? 1 : 0;',
            'name'      => 'is_active',
        ));

        if(Mage::getStoreConfig('xwarehouse/general/enabled')==1){
            $data_warehouse = Mage::getModel('xwarehouse/warehouse')->getCollection();
            $listWarehouse = array();
            foreach($data_warehouse as $row){
                $listWarehouse[] = array('value' => $row->getWarehouseId() , 'label' => $row->getLabel());
            }
        }else{
            $listWarehouse[] = array('value' => 0 , 'label' => 'Default');
        }

        $fieldset->addField('warehouse_id', 'select', array(
            'label' => Mage::helper('xpos')->__('Warehouse'),
            'values' => $listWarehouse,
            'class'     => 'required-entry',
            'required'  => true,
            'name' => 'warehouse_id',
        ));

        if (Mage::getSingleton('adminhtml/session')->getTillData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getTillData());
            Mage::getSingleton('adminhtml/session')->getTillData(null);
        } elseif (Mage::registry('till_data')) {
            $form->setValues(Mage::registry('till_data')->getData());
            $formData = Mage::registry('till_data')->getData();
            $form->getElement('is_active')->setIsChecked(!empty($formData['is_active']));
        }

    }
}