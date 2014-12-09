<?php
/**
 * SmartOSC Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 *
 * @category   SM
 * @package    SM_XPOS
 * @version    2.3.1
 * @author     truongnq@smartosc.com
 * @copyright  Copyright (c) 2010-2013 SmartOSC Co. (http://www.smartosc.com)
 */
class SM_XPos_Model_Config_Producttypes
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {

        $types = Mage::getSingleton('catalog/product_type')->getOptionArray();
        foreach ($types as $key => $value) {
            $array[] = array('value' => $key, 'label' => $value);
        }

        return $array;
    }

}
