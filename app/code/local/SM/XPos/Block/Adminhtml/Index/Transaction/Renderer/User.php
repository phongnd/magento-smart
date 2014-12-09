<?php

class SM_XPos_Block_Adminhtml_Sales_Order_Create_Transaction_Renderer_User extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{

    public function render(Varien_Object $row)
    {
        $getData = $row->getData();
        $userId = $getData['user_id'];
        $user = Mage::getModel('admin/user')->load($userId);
        return $user->getUsername();
    }
}