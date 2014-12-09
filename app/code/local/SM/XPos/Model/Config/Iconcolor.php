<?php
/**
 * Created by PhpStorm.
 * User: Smartor
 * Date: 6/25/14
 * Time: 2:07 PM
 */
class SM_XPos_Model_Config_Iconcolor
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {

        return array(
            array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('Color light')),
            array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('Color dark ')),
        );

        return $array;
    }

}