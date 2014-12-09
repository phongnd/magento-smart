<?php
/**
 * Author: HieuNT
 * Email: hieunt@smartosc.com
 */

class SM_XPos_Block_Report_Sales_Sales extends Mage_Adminhtml_Block_Widget_Grid_Container {
    public function __construct()
    {		
        $this->_controller = 'report_sales_sales';
        $this->_blockGroup = 'xpos';
        $this->_headerText = Mage::helper('reports')->__('Total Ordered Report');
        parent::__construct();
        $this->setTemplate('report/grid/container.phtml');
        $this->_removeButton('add');
        $this->addButton('filter_form_submit', array(
            'label'     => Mage::helper('reports')->__('Show Report'),
            'onclick'   => 'filterFormSubmit()'
        ));
    }
}