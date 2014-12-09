<?php
/**
 * Created by PhpStorm.
 * User: smartosc
 * Date: 12/8/14
 * Time: 1:38 PM
 */
class GHA_Product_Model_Observer extends Mage_Core_Model_Abstract
{
	public function catalogProductNewAction($observer){
		Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);
		$product = $observer->getProduct();
//		$recipientEmailData = array(
//			'is_delete' => 0,
//			'is_require' => 0,
//			'title' => "Recipient's Email Address",
//			'type' => 'field',
//			'sort_order' => 7,
//			'price_type'        => 'fixed',
//			'price'             => '0',
//		);

//		Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);
//		$websiteIds = array(1);
//		$typeId = 'simple';
//		$attributeSetId = '4';
//		$name = 'New Screen';
//		$description = 'New Screen';
//		$shortDescription = 'New Screen';
//		$sku = 'screen';
//		$status = 1;
//		$visibility = 4;
//		$price = '0';
//		$taxClassId = 0;
//		$stockData = array(
//			'manage_stock' => 1,
//			'is_in_stock' => 1,
//			'qty' => 10000000,
//		);
//
//		$product2 = Mage::getModel('catalog/product');
//		$product2->setWebsiteIds($websiteIds)
//		        ->setStoreId(1)
//		        ->setAttributeSetId($attributeSetId)
//		        ->setTypeId($typeId)
//		        ->setName($name)
//		        ->setDescription($description)
//		        ->setShortDescription($shortDescription)
//		        ->setSku($sku)
//				->setWeight('0')
//		        ->setStatus($status)
//		        ->setVisibility($visibility)
//		        ->setPrice($price)
//		        ->setTaxClassId($taxClassId)
//		        ->setStockData($stockData);
//
//		$product->setHasOptions(1);
//		$optionInstance = $product->getOptionInstance()->unsetOptions();
//		$optionInstance->addOption($recipientEmailData);
//		$optionInstance->setProduct($product);
//		$product2->save();

//		$url = Mage::getUrl("*/*/edit", array('id' => 2,'key'=>'e23d84a782241d42fc5a4b45e0419160'));
//		$this->_redirectUrl($url);

//		$product->setCustomOptions(array($recipientEmailData));
//		$product->setCanSaveCustomOptions(true);

//		$options = Mage::getModel('catalog/product_option')->addData($recipientEmailData);
//		$product->addOption($options);

//		$product_entity_table = $product->getResource()->getEntityTable();
//		$resource = Mage::getSingleton('core/resource');
//		$connection = $resource->getConnection('core_read');
//		$result = $connection->showTableStatus($product_entity_table);
//		$next_product_id = $result['Auto_increment'];

//		$opt = Mage::getModel('catalog/product_option');
//		$opt->setProduct($product);
//        $opt->addOption($recipientEmailData);
//		$opt->setProductId($next_product_id);
//        $opt->saveOptions();

//		$product->setProductOptions(array($recipientEmailData));
//		$product = Mage::getModel('catalog/product')->load(Mage::getModel("catalog/product")->getIdBySku($sku));

//		$product->setName("abc");
//		Mage::log($product);
//		Mage::log(Mage::getModel("catalog/product")->load(2));
//		die;
	}
}