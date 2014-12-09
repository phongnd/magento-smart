<?php

class SM_XPos_Block_Adminhtml_Catalog_Report_Render_Till extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row) {
        $tillId = $row->getData('till_id');
        if (!empty($tillId)) {
            $user = Mage::getModel('xpos/till')->load($tillId);
            return $user->getData('till_name');
        }
    }
}