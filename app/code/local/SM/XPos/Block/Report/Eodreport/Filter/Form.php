<?php
/**
 * Created by PhpStorm.
 * User: Le Nam
 * Date: 10/23/14
 * Time: 2:22 PM
 */
class SM_XPos_Block_Report_Eodreport_Filter_Form extends Mage_Sales_Block_Adminhtml_Report_Filter_Form_Order {
    protected function _prepareForm()
    {
        $superGrandParent = get_parent_class(get_parent_class(get_parent_class(get_parent_class($this))));

        $actionUrl = $this->getUrl('*/*/sales');
        $form = new Varien_Data_Form(
            array('id' => 'filter_form', 'action' => $actionUrl, 'method' => 'get')
        );
        $htmlIdPrefix = 'sales_report_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('reports')->__('Filter')));

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        $fieldset->addField('store_ids', 'hidden', array(
            'name'  => 'store_ids'
        ));


        $fieldset->addField('from', 'date', array(
            'name'      => 'from',
            'format'    => $dateFormatIso,
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'label'     => Mage::helper('reports')->__('From'),
            'title'     => Mage::helper('reports')->__('From'),
            'required'  => true
        ));

        $fieldset->addField('to', 'date', array(
            'name'      => 'to',
            'format'    => $dateFormatIso,
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'label'     => Mage::helper('reports')->__('To'),
            'title'     => Mage::helper('reports')->__('To'),
            'required'  => true
        ));

//        $fieldset->addField('period_type', 'select', array(
//            'name' => 'period_type',
//            'options' => array(
//                'custom'   => Mage::helper('reports')->__('None'),
//                'day'   => Mage::helper('reports')->__('Day'),
//                'month' => Mage::helper('reports')->__('Month'),
//                'year'  => Mage::helper('reports')->__('Year')
//            ),
//            'label' => Mage::helper('reports')->__('Group by period'),
//            'title' => Mage::helper('reports')->__('Group by period')
//        ));

        $data_till = Mage::getModel('xpos/till')->getCollection();
        $listTill = array();
        $listTill[] = array('value' => 0 , 'label' => 'Any');
        foreach($data_till as $row){
            $listTill[] = array('value' => $row->getTillId() , 'label' => $row->getTillName());
        }
        $fieldset->addField('till_id', 'select', array(
            'label' => Mage::helper('xpos')->__('Till'),
            'values' => $listTill,
            'name' => 'till_id',
        ));

        if(Mage::getStoreConfig('xpos/general/integrate_xmwh_enabled')){
            $data_warehouse = Mage::getModel('xwarehouse/warehouse')->getCollection();
            $lstWarehouse = array();
            $lstWarehouse[] = array('value' => 0 , 'label' => 'Any');
            foreach($data_warehouse as $row){
                $lstWarehouse[] = array('value' => $row->getWarehouseId() , 'label' => $row->getLabel());
            }
            $fieldset->addField('warehouse_id', 'select', array(
                'label' => Mage::helper('xpos')->__('Warehouse'),
                'values' => $lstWarehouse,
                'name' => 'warehouse_id',
            ));
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return $this;
    }
}