<?php
/**
 * Created by PhpStorm.
 * User: thangnv
 * Date: 1/16/14
 * Time: 10:58 AM
 */ 
class SM_XPos_Model_Resource_Catalog_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection {
    /**
     * Initialize resources
     *
     */
    protected function _construct()
    {
        Mage::app()->setCurrentStore(1);
        return parent::_construct();
    }

    /**
     * Retrieve is flat enabled flag
     * Return alvays false if magento run admin
     *
     * @return bool
     */
    public function isEnabledFlat()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            return true;
        }
        if (!isset($this->_flatEnabled[$this->getStoreId()])) {
            $this->_flatEnabled[$this->getStoreId()] = $this->getFlatHelper()
                ->isEnabled($this->getStoreId());
        }
        return $this->_flatEnabled[$this->getStoreId()];
    }
}