<?php

class SM_Core_Model_Observer extends Mage_Core_Model_Abstract {

    // refresh license status after updating
    public function refreshStatus($observer) {
        ob_start();
        $product = explode("_", $observer['event']['name']);
        $product = $product[count($product) - 1];
        /*if ($product == "barcode")
            $product2 = SM_Barcode_Helper_Abstract::PRODUCT;
        else if ($product == 'xwarehouse') {
            $product2 = SM_XWarehouse_Helper_Abstract::PRODUCT_NAME;
        } else $product2 = $product;*/

        switch($product){
            case "barcode":
                $product2 = SM_Barcode_Helper_Abstract::PRODUCT;
                break;
            case "xwarehouse":
                $product2 = SM_XWarehouse_Helper_Abstract::PRODUCT_NAME;
                break;
            case "xb2b":
                $product2 = "X-B2B";
                break;
            default:
                $product2 = $product;
                break;
        }

        // remove old local key
        $dir = Mage::getBaseDir("var") . DS . "smartosc" . DS . strtolower(substr($product2, 0, 5)) . DS;
        $filepath = $dir . "license.dat";
        $file = new Varien_Io_File;
        $file->rm($filepath);
        Mage::helper('smcore')->checkLicense($product2, Mage::getStoreConfig($product . '/general/key'), true);
        Mage::getConfig()->cleanCache();
    }

}
