<?php
/**
 * Date: 1/28/13
 * Time: 5:00 PM
 */

class SM_XPos_Model_Config_Style {

    public function toOptionArray()
    {
        return array(
            array('value'=>'invoice', 'label'=>Mage::helper('adminhtml')->__('210mm (A4)')),
            array('value'=>'80mm', 'label'=>Mage::helper('adminhtml')->__('80mm')),
            array('value'=>'58mm', 'label'=>Mage::helper('adminhtml')->__('58mm')),
            array('value'=>'receipt', 'label'=>Mage::helper('adminhtml')->__('40mm')),
        );
    }
}