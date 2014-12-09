<?php
/**
 * Author: Hieunt
 * Date: 3/19/13
 * Time: 6:29 PM
 */

class SM_XPos_Block_Adminhtml_Catalog_Report_Render_User extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row) {
        $userId = $row->getData('xpos_user_id');
        if (!empty($userId)) {
            $user = Mage::getModel('xpos/user')->load($userId);
            return $user->getData('firstname'). " " . $user->getData('lastname');
        }
    }
}