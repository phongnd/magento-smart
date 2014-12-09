<?php
class SM_XPos_Block_Adminhtml_Override_Ordermessage extends Mage_Adminhtml_Block_Template
{
    public function getMessages()
    {
        $message = '';
        $action = $this->getOrderAction();
        $order = Mage::getModel('sales/order')->load($this->getOrder());
        switch($action){
            case 'invoice':
                $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
                $invoice->register();
                $invoice->getOrder()->setCustomerNoteNotify(false);
                $invoice->getOrder()->setIsInProcess(true);

                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());
                $transactionSave->save();
                $message = 'The invoice has been created.';
                break;

            case 'ship':
                $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment();
                $shipment->register();
                $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                $shipment->getOrder()->setIsInProcess(true);
                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($shipment)
                    ->addObject($shipment->getOrder())
                    ->save();
                $message = 'The shipment has been created.';
                break;

            case 'canceled':
                $order->cancel()
                    ->save();
                $message = 'The order has been cancelled.';
                break;
        }
        return $message;
    }


}