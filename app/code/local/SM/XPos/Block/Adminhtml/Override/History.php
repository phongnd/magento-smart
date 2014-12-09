<?php
class SM_XPos_Block_Adminhtml_Override_History extends Mage_Adminhtml_Block_Sales_Order_View_History
{
    protected function _prepareLayout()
    {
        $onclick = "submitComment($('order_history_block'), '".$this->getSubmitUrl()."')";
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => Mage::helper('sales')->__('Submit Comment'),
                'class'   => 'save',
                'onclick' => $onclick
            ));
        $this->setChild('submit_button', $button);
        $grandparent = get_parent_class(get_parent_class($this));
        return $grandparent::_prepareLayout();
    }

    public function getSubmitUrl()
    {
        $_order = $this->getOrder();
        if(!empty($_order)) {
            return parent::getSubmitUrl();
        } else {
            $_orderId = Mage::app()->getRequest()->getParam('orderId');
            $_order = Mage::getModel('sales/order')->load($_orderId);
            // Set Order to Registry
            Mage::register('sales_order', $_order);
            return $this->getUrl('*/*/addComment', array('order_id'=>$_order->getId()));
        }

    }
}
?>