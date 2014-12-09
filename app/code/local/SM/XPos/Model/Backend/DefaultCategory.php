<?php
/**
 * Created by PhpStorm.
 * User: Smartor
 * Date: 6/13/14
 * Time: 11:48 AM
 */
class SM_XPos_Model_Backend_DefaultCategory extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    public function beforeSave($object) {
        static $i=1;
        if($i==1){
            $attributeCode = $this->getAttribute()->getName();
            if($attributeCode=='xpos_default'){
                $categories = Mage::getModel('catalog/category')
                    ->getCollection()
                    ->addAttributeToFilter('xpos_enable', true)
                    ->addAttributeToFilter('xpos_default',true)
                    ->addAttributeToSelect('*');

                $category_id = Mage::getSingleton('catalog/layer')
                    ->getCurrentCategory()
                    ->getId();

                foreach($categories as $cate){

                    $id = $cate->getData('entity_id');
                    $category = Mage::getModel('catalog/category')->load($id);
                    $category->setXposDefault(0);
                    $i++;
                    $category->save();
                }
                return $this;
            }

        }

    }

}