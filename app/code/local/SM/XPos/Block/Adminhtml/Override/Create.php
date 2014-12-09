<?php
class SM_XPos_Block_Adminhtml_Override_Create extends Mage_Adminhtml_Block_Sales_Order_Creditmemo_Create
{
    public function __construct()
    {
        $this->_objectId = 'order_id';
        $this->_controller = 'sales_order_creditmemo';
        $this->_mode = 'create';

        parent::__construct();

        $this->_removeButton('back');
        $this->_removeButton('reset');

        /*$this->_addButton('submit_creditmemo', array(
            'label'     => Mage::helper('sales')->__('Submit Credit Memo'),
            'class'     => 'save submit-button',
            'onclick'   => '$(\'edit_form\').submit()',
            )
        );*/

    }
}