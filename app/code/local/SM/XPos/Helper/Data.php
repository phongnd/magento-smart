<?php
class SM_XPos_Helper_Data extends Mage_Core_Helper_Abstract {
    public function __construct() {
      //Mage::helper('smcore')->checkLicense('xpos', Mage::getStoreConfig('xpos/general/key'));
    }    
    public function isEnableModule() {
        return Mage::getStoreConfig('xpos/general/enabled');
    }
    
    public function aboveVersion($version)
    {
        $info = Mage::getVersionInfo();
        
        //Enterprise 1.10 is equivalent to Community 1.4
        if($info['major'] == 1 && $info['minor'] == 10) {
            $info['minor'] = 4;
        }
        
        $version = explode('.', $version);
        return intval($info['major']) >= intval($version[0]) && intval($info['minor']) >= intval($version[1]); 
    }

    public function checkEE()
    {
        return Mage::getEdition() == Mage::EDITION_ENTERPRISE;
    }

    public function isWarehouseIntegrate() {
        if (Mage::getStoreConfig('xwarehouse/general/enabled') == 1 && Mage::getStoreConfig('xpos/general/integrate_xmwh_enabled') == 1) {
            return 1;
        }
        return 0;
    }

    public function isXposLoginEnabled() {
        if (Mage::getStoreConfig('xpos/general/enabled') == 1 && Mage::getStoreConfig('xpos/general/enabled_cashier') == 1) {
            return 1;
        }
        return 0;
    }

    public function isEmailConfirmationEnabled(){
        return Mage::getStoreConfig('xpos/receipt/enabled');
    }

    /**
     * Call this function to add jquery on template
     */
    public function addJQuery($layout) {
        $head = $layout->getBlock('head');
        $jqueryPath = 'sm/jquery-1.9.1.js';
        $head->addJs($jqueryPath);
    }

    public function columnExist($tableName,$columnName) {
        $resource = Mage::getSingleton('core/resource');
        $writeAdapter = $resource->getConnection('core_write');

        Zend_Db_Table::setDefaultAdapter($writeAdapter);
        $table = new Zend_Db_Table($tableName);
        if (!in_array($columnName,$table->info('cols'))) {
            return false;
        } return true;
    }

    public function tableExist($tableName) {
        $exists = (boolean) Mage::getSingleton('core/resource')
            ->getConnection('core_write')
            ->showTableStatus(trim($tableName,'`'));
        return $exists;
    }

}

