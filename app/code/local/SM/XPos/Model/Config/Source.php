<?php
/**
 * User: Hieunt
 * Date: 3/8/13
 * Time: 3:13 PM
 */

class SM_XPos_Model_Config_Source {
    public function toOptionArray() {
        $result = null;
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter();
        if ($attributes != null && $attributes->count() > 0):
            $result[] = array('value' => 'entity_id' ,'label' => 'ID');
            foreach ($attributes as $item):
                 $result[] = array('value' => $item->getAttributeCode(), 'label' => Mage::helper('xpos')->__($item->getFrontendLabel()));
            endforeach;
        endif;
        return $result;
    }
}