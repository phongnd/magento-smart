<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales order create items grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class SM_XPos_Block_Adminhtml_Override_Grid extends Mage_Adminhtml_Block_Sales_Order_Create_Items_Grid
{
    /**
     * Get order item extra info block
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @return Mage_Core_Block_Abstract
     */
    public function getItemExtraInfo($item)
    {
        return $this->getLayout()
            ->getBlock('order_item_extra_info')
            ->setItem($item);
    }

    public function updateTax() {
        
        $_items = $this->getItems();
        if (empty($_items)) return;

        foreach($_items as $_item) {
            if ($_item->getProductType() != 'bundle') continue;
            if ($_item->getTaxPercent() == 0 || $_item->getTaxPercent() == '') {
                $price = $_item->getPrice();
                $originalPrice = $this->helper('checkout')->getSubtotalInclTax($_item)/$_item->getQty();
                if ($price != $originalPrice) {
                    $tax = floatval($originalPrice/$price) - 1;
                    $_item->setTaxPercent(round($tax*100,2));
                }
            }

        }
    }
}
