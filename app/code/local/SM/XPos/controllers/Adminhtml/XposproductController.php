<?php

class SM_XPos_Adminhtml_XposproductController extends Mage_Adminhtml_Controller_Action
{
    public function loadAction()
    {
        $page = $this->getRequest()->getParam('page');
        $warehouseId = $this->getRequest()->getParam('warehouse');
        $is_integrate_with_MWH = Mage::helper('xpos')->isWarehouseIntegrate();
        if (!empty($is_integrate_with_MWH) && !empty($warehouseId)) {
            $this->_getSession()->setWarehouseId($warehouseId);
        }else{
            $warehouseId = '';
        }

        $cacheKey = 'xpos_'.$page.'_'.$warehouseId;
        $cache = Mage::app()->getCache()->load($cacheKey);
        //$cache = false;
        if (!$cache){
            $result = Mage::helper('xpos/product')->getProductList($this,$page,$warehouseId);
            $cache = Mage::helper('core')->jsonEncode($result);
            Mage::app()->getCache()->save($cache,$cacheKey,array("xpos_cache"),Mage::getStoreConfig('xpos/offline/data_reload_interval'));
        }
        echo $cache;
    }
    public function searchAction(){
        $query = $this->getRequest()->getParam('query');
       // $warehouseId = $this->getRequest()->getParam('warehouse');
        $warehouseId = Mage::getSingleton('adminhtml/session')->getWarehouseId();

        $is_integrate_with_MWH = Mage::helper('xpos')->isWarehouseIntegrate();
        if (!empty($is_integrate_with_MWH) && !empty($warehouseId)) {
            $this->_getSession()->setWarehouseId($warehouseId);
        }else{
            $warehouseId = '';
        }

        $result = Mage::helper('xpos/product')->searchProduct($this,$query,$warehouseId);
        echo Mage::helper('core')->jsonEncode($result);
    }

    public function testAction(){
        $warehouseId = $this->getRequest()->getParam('wid');
        $this->_getSession()->setWarehouseId($warehouseId);
        $result = Mage::helper('xpos/product')->getWarehouseProduct($warehouseId);
        var_dump($result);

        $productId = $this->getRequest()->getParam('pid');
        $product = Mage::getModel('catalog/product')->load($productId);
        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        echo 'Product '.$productId.' '.$stock->getQty().' :'.$product->getData('is_in_stock').' stock';
        var_dump($product->getData());
    }

    public function configAction()
    {
        Mage::helper('catalog/product')->setSkipSaleableCheck(true);
//        $update->resetHandles();
//        $update->addHandle('ADMINHTML_XPOS_CATALOG_PRODUCT_COMPOSITE_CONFIGURE');
//
//        $product = Mage::getModel('catalog/product')->load(164);
//        Mage::register('current_product', $product);
//        Mage::register('product', $product);
//
//        $productType = $product->getTypeId();
//        $update->addHandle('XPOS_PRODUCT_TYPE_' . $productType);
//        $this->loadLayoutUpdates()->generateLayoutXml()->generateLayoutBlocks();
//        $str = $this->getLayout()->getOutput();
//        echo $str;

        $productCollection = Mage::getResourceModel('catalog/product_collection')
            ->setStoreId(1)
            ->addAttributeToSelect('*')
            ->setOrder('created_at', 'asc')->setCurPage(1)->setPageSize(5);
        $productCollection->addAttributeToFilter('entity_id', array('gt' => 20));
        $productCollection->load();

        foreach ($productCollection as $product) {
            echo $product->getId().' '.$product->getName().'<br>';
            $product = Mage::getModel('catalog/product')->load($product->getId());
            Mage::unregister('current_product');
            Mage::unregister('product');
            Mage::register('current_product', $product);
            Mage::register('product', $product);
            $update = $this->getLayout()->getUpdate();
            $update->resetHandles();
            $update->addHandle('ADMINHTML_XPOS_CATALOG_PRODUCT_COMPOSITE_CONFIGURE');

            $productType = $product->getTypeId();
            $update->addHandle('XPOS_PRODUCT_TYPE_' . $productType);
            $this->loadLayoutUpdates()->generateLayoutXml()->generateLayoutBlocks();
            $options = $this->getLayout()->getOutput();
            echo $options.'<br><br>';
        }
        die;

        $product = Mage::getModel('catalog/product')->load(26);
        Mage::unregister('current_product');
        Mage::unregister('product');
        Mage::register('current_product', $product);
        Mage::register('product', $product);
        $update = $this->getLayout()->getUpdate();
        $update->resetHandles();
        $update->addHandle('ADMINHTML_XPOS_CATALOG_PRODUCT_COMPOSITE_CONFIGURE');

        $productType = $product->getTypeId();
        $update->addHandle('XPOS_PRODUCT_TYPE_' . $productType);
        $this->loadLayoutUpdates()->generateLayoutXml()->generateLayoutBlocks();
        $options = $this->getLayout()->getOutput();
        echo $options;
    }

}
