<?php
/**
 * Author: HieuNT
 * Email: hieunt@smartosc.com
 */

class SM_XPos_Block_Adminhtml_Report_Filter_Form_Order extends Mage_Sales_Block_Adminhtml_Report_Filter_Form_Order {
    protected function _prepareForm()
    {
        //$superGrandParent = get_parent_class(get_parent_class(get_parent_class(get_parent_class($this))));

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

        $fieldset->addField('report_type', 'select', array(
            'name'      => 'report_type',
            'options'   => $this->_reportTypeOptions,
            'label'     => Mage::helper('reports')->__('Match Period To'),
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

        /*$fieldset->addField('show_empty_rows', 'select', array(
            'name'      => 'show_empty_rows',
            'options'   => array(
                '1' => Mage::helper('reports')->__('Yes'),
                '0' => Mage::helper('reports')->__('No')
            ),
            'label'     => Mage::helper('reports')->__('Empty Rows'),
            'title'     => Mage::helper('reports')->__('Empty Rows')
        ));*/

        $statuses = Mage::getModel('sales/order_config')->getStatuses();
        $values = array();
        foreach ($statuses as $code => $label) {
            if (false === strpos($code, 'pending')) {
                $values[] = array(
                    'label' => Mage::helper('reports')->__($label),
                    'value' => $code
                );
            }
        }

        $fieldset->addField('show_order_statuses', 'select', array(
            'name'      => 'show_order_statuses',
            'label'     => Mage::helper('reports')->__('Order Status'),
            'options'   => array(
                '0' => Mage::helper('reports')->__('Any'),
                '1' => Mage::helper('reports')->__('Specified'),
            ),
            'note'      => Mage::helper('reports')->__('Applies to Any of the Specified Order Statuses'),
        ), 'to');

        $fieldset->addField('order_statuses', 'multiselect', array(
            'name'      => 'order_statuses',
            'values'    => $values,
            'display'   => 'none'
        ), 'show_order_statuses');

        // define field dependencies
        if ($this->getFieldVisibility('show_order_statuses') && $this->getFieldVisibility('order_statuses')) {
            $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                    ->addFieldMap("{$htmlIdPrefix}show_order_statuses", 'show_order_statuses')
                    ->addFieldMap("{$htmlIdPrefix}order_statuses", 'order_statuses')
                    ->addFieldDependence('order_statuses', 'show_order_statuses', '1')
            );
        }

        $data_till = Mage::getModel('xpos/till')->getCollection();
        $listTill = array();
        $listTill[] = array('value' => 0 , 'label' => 'Any');

        foreach($data_till as $row){
            $listTill[] = array('value' => $row->getTillId() , 'label' => $row->getTillName());
        }

        if(Mage::getStoreConfig('xpos/general/enable_till') == 1)
        $fieldset->addField('till_id', 'select', array(
            'label'  => Mage::helper('xpos')->__('Till'),
            'values' => $listTill,
            'name'   => 'till_id',
        ));

        if(Mage::getStoreConfig('xpos/general/integrate_xmwh_enabled')){
            $data_warehouse = Mage::getModel('xwarehouse/warehouse')->getCollection();
            $lstWarehouse = array();
            $lstWarehouse[] = array('value' => 0 , 'label' => 'Any');
            foreach($data_warehouse as $row){
                $lstWarehouse[] = array('value' => $row->getWarehouseId() , 'label' => $row->getLabel());
            }
            $fieldset->addField('warehouse_id', 'select', array(
                'label'  => Mage::helper('xpos')->__('Warehouse'),
                'values' => $lstWarehouse,
                'name'   => 'warehouse_id',
            ));
        }
        $action = array();
        $action['orderlist'] = Mage::helper('reports')->__('Orderlist');
        $action['payment'] = Mage::helper('reports')->__('Payment Method');

        if(Mage::getStoreConfig('xpos/general/enabled_cashier') == 1)
            $action['cashier'] = Mage::helper('reports')->__('Per Cashier');
        if(Mage::getStoreConfig('xpos/general/enable_till') == 1)
            $action['till'] = Mage::helper('reports')->__('Per Till');
        if(Mage::getStoreConfig('xpos/general/integrate_xmwh_enabled') == 1)
            $action['warehouse'] = Mage::helper('reports')->__('Per Warehouse');

        $fieldset->addField('report_by_type', 'select', array(
            'name' => 'report_by_type',
            'options' => $action,
            'label' => Mage::helper('reports')->__('Type'),
            'title' => Mage::helper('reports')->__('Type')
        ));

        $fieldset->addField('xpos_only', 'checkbox', array(
            'label'     => Mage::helper('xpos')->__('XPOS Only'),
            'onclick'   => 'this.value = this.checked ? 1 : 0;',
            'checked'     => 'checked',
            'name'      => 'xpos_only',
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return $this;
    }
}