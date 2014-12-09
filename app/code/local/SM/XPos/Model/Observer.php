<?php
/**
 * Date: 1/28/13
 * Time: 4:54 PM
 */

class SM_XPos_Model_Observer extends Mage_Core_Model_Abstract
{
    static $orderAfterSave = false;
    static $invoiceSaveAfter = false;
    /**
     * Style of invoice change after change save config
     */
    public function styleChanging($observer)
    {
        $style = Mage::getStoreConfig('xpos/receipt/style_for_printing_invoice');
        if ($style == 'invoice') {
            Mage::getModel('core/config')->saveConfig('xpos/receipt/printing_invoice', 1);
            Mage::getModel('core/config')->saveConfig('xpos/receipt/printing_receipt', 0);
            Mage::getModel('core/config')->saveConfig('xpos/receipt/printing_receipt_80mm', 0);
            Mage::getModel('core/config')->saveConfig('xpos/receipt/printing_receipt_58mm', 0);
        } else if ($style == 'receipt') {
            Mage::getModel('core/config')->saveConfig('xpos/receipt/printing_invoice', 0);
            Mage::getModel('core/config')->saveConfig('xpos/receipt/printing_receipt', 1);
            Mage::getModel('core/config')->saveConfig('xpos/receipt/printing_receipt_80mm', 0);
            Mage::getModel('core/config')->saveConfig('xpos/receipt/printing_receipt_58mm', 0);
        } else if ($style == '80mm') {
            Mage::getModel('core/config')->saveConfig('xpos/receipt/printing_invoice', 0);
            Mage::getModel('core/config')->saveConfig('xpos/receipt/printing_receipt', 0);
            Mage::getModel('core/config')->saveConfig('xpos/receipt/printing_receipt_80mm', 1);
            Mage::getModel('core/config')->saveConfig('xpos/receipt/printing_receipt_58mm', 0);
        } else if ($style == '58mm') {
            Mage::getModel('core/config')->saveConfig('xpos/receipt/printing_invoice', 0);
            Mage::getModel('core/config')->saveConfig('xpos/receipt/printing_receipt', 0);
            Mage::getModel('core/config')->saveConfig('xpos/receipt/printing_receipt_80mm', 0);
            Mage::getModel('core/config')->saveConfig('xpos/receipt/printing_receipt_58mm', 1);
        } else {
            Mage::getModel('core/config')->saveConfig('xpos/receipt/printing_invoice', 1);
            Mage::getModel('core/config')->saveConfig('xpos/receipt/printing_receipt', 0);
            Mage::getModel('core/config')->saveConfig('xpos/receipt/printing_receipt_80mm', 0);
            Mage::getModel('core/config')->saveConfig('xpos/receipt/printing_receipt_58mm', 0);
        }
    }

    public function customerSave()
    {
        $dir = Mage::getBaseDir('media') . DS . 'smartosc';
        $customer_file = $dir . DS . 'customer.json';
        $last_modify = filemtime($customer_file);
        $period = time() - $last_modify;
        $check_validate = $period > 86400 ? true : false;
        if ($last_modify == false || $check_validate) {
            $searchInstance = new SM_XPos_Model_Adminhtml_Search_Customer;
            $results = $searchInstance
                ->loadAll('all', 1);
            if (!empty($results)) {
                $json = '';
                $json = json_encode($results->getResults());
                //Check dir permission
                if (!is_dir_writeable($dir)) {
                    $file = new Varien_Io_File;
                    $file->checkAndCreateFolder($dir);
                }
                //Save to file
                file_put_contents($dir . DS . 'customer.json', $json);
            }
        }
    }

    public function orderSaveAfter($observer) {
        $isXpos = Mage::app()->getRequest()->getParam('xpos');
        $xpos_user_id = Mage::app()->getRequest()->getParam('xpos_user_id');
        $till_id = Mage::app()->getRequest()->getParam('till_id');
        if ((!empty($isXpos) || !empty($xpos_user_id)) && !self::$orderAfterSave) {
            self::$orderAfterSave = true;
            $order = $observer->getEvent()->getOrder();
            if (!empty($isXpos)) {
                $order->setData('xpos',1);
            }
            if (!empty($xpos_user_id)) {
                $order->setData('xpos_user_id',$xpos_user_id);
            }
            if (!empty($till_id)) {
                $order->setData('till_id',$till_id);
            }
            $order->save();
        }
    }

    public function invoiceSaveAfter($observer) {

        $xpos_user_id = Mage::app()->getRequest()->getParam('xpos_user_id');
        if (!empty($xpos_user_id) && !self::$invoiceSaveAfter) {
            self::$invoiceSaveAfter = true;
            $invoice = $observer->getEvent()->getInvoice();
            if (!empty($xpos_user_id)) {
                $invoice->setData('xpos_user_id',$xpos_user_id);
            }
            $invoice->save();
        }
    }

    public function refreshLifetime($observer) {
        $collection = Mage::getModel('sales/order')->getCollection();
        $collection->addFieldToFilter('xpos',array('notnull' => 1));
        $write = Mage::getSingleton("core/resource")->getConnection("core_write");
        $tableName = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
        foreach($collection as $item) {
            $createAt = $item->getData('created_at');
            $updateAt = $item->getData('updated_at');
            $query = "UPDATE {$tableName} SET tzo_created_at = '{$createAt}', tzo_updated_at = '{$updateAt}' WHERE entity_id = ".$item->getEntityId();
            $write->query($query);
        }
    }
}