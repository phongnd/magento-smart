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
 * @version    2.1.12
 * @author     truongnq@smartosc.com
 * @copyright  Copyright (c) 2010-2013 SmartOSC Co. (http://www.smartosc.com)
 */
class SM_XPos_Model_Config_Integrate
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {

        if(Mage::getStoreConfig('xwarehouse/general/enabled')==1){
            $array = array(
                array('value' => 1, 'label'=>'Enable'),
                array('value' => 0, 'label'=>'Disable'),
            );

        } else{
            $array = array(
                array('value' => 0, 'label'=>'Disable')
            );
        }

        return $array;
    }

}
