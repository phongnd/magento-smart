<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Hieu
 * Date: 3/4/13
 * Time: 2:51 PM
 */

class SM_Core_Block_Template extends Mage_Adminhtml_Block_Template {
    public function addJquery($layout) {
        $helper = Mage::helper('xpos');
        if ($layout)
            $helper->addJQuery($layout);
        return $this;
    }

    public function createProduct() {
        $param = $this->getRequest()->getParams('loop');
        if (!$param) return;
        $i = $param;
        while($i>$param-50) {
            $product = new Mage_Catalog_Model_Product();
            $i = $i - 1;
            $product->setSku('some-sku-value-here'.$i);
            $product->setAttributeSetId('9');# 9 is for default
            $product->setTypeId('simple');
            $product->setName('Some cool product name'.$i);
            $product->setCategoryIds(array(42)); # some cat id's,
            $product->setWebsiteIDs(array(1)); # Website id, 1 is default
            $product->setDescription('Full description here');
            $product->setShortDescription('Short description here');
            $product->setPrice(39.99); # Set some price

//Default Magento attribute
            $product->setWeight(4.0000);

            $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
            $product->setStatus(1);
            $product->setTaxClassId(0); # default tax class
            $product->setStockData(array(
                'is_in_stock' => 1,
                'qty' => 99999
            ));

            $product->setCreatedAt(strtotime('now'));

            try {
                $product->save();
            }
            catch (Exception $ex) {

            }
        }
    }
}