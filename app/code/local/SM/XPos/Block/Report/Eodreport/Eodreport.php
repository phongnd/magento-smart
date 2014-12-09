<?php
/**
 * Created by PhpStorm.
 * User: Smartor
 * Date: 10/23/14
 * Time: 9:27 AM
 */
class SM_XPos_Block_Report_Eodreport_Eodreport extends Mage_Adminhtml_Block_Widget_Grid_Container {
    public function __construct()
    {
        $this->_controller = 'report_eodreport_eodreport';
        $this->_blockGroup = 'xpos';
        $this->_headerText = Mage::helper('reports')->__('End of day Report');
        parent::__construct();
        $this->setTemplate('report/grid/container.phtml');
        $this->_removeButton('add');
        $this->addButton('filter_form_submit', array(
            'label'     => Mage::helper('reports')->__('Show Report'),
            'onclick'   => 'filterFormSubmit()'
        ));
    }
}