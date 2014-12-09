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
class SM_XPos_Model_Config_Productvisibility
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $array = array(
            array('value' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE, 'label'=>'Not Visible Individually'),
            array('value' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG, 'label'=>'Catalog'),
            array('value' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH, 'label'=>'Search'),
            array('value' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH, 'label'=>'Catalog, Search'),
        );

        return $array;
    }

}
