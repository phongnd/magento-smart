<?php
/**
 * Created by PhpStorm.
 * User: Smartor
 * Date: 10/16/14
 * Time: 2:47 PM
 */

class SM_XPos_Block_Adminhtml_Report_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_headersVisibility = true;
    protected $_pagerVisibility = true;

    public function __construct($attributes=array())
    {
        /**
         * Override from grandparent to prevent setTemplate from parent
         */

        //$this->setTemplate('/grid.phtml');
        parent::__construct();
        $this->setRowClickCallback('openGridRow');
        $this->_emptyText = Mage::helper('adminhtml')->__('No records found.');
        $this->setId('sales_order_create_report_list');
        $this->setUseAjax(true);
        $this->setDefaultSort('date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(false);

    }


    protected function _prepareCollection()
    {
        $collection = Mage::getSingleton('xpos/report')->getCollection();

        $select = $collection->getSelect();
        if(Mage::getStoreConfig('xpos/general/enable_till') == 1)
        $select->joinLeft(array('A' => $collection->getTable('xpos/till')),'`main_table`.`till_id` = `A`.`till_id`',array('till_name'));

        if(Mage::getStoreConfig('xpos/general/enabled_cashier') == 1)
        $select->joinLeft(array('B' => $collection->getTable('xpos/user')),'`main_table`.`cashier_id` = `B`.`xpos_user_id`',array('lastname','firstname'));

        if(Mage::getStoreConfig('xpos/general/integrate_xmwh_enabled') == 1){
            $select->joinLeft(array('C' => $collection->getTable('xwarehouse/warehouse')),'`main_table`.`warehouse_id` = `C`.`warehouse_id`',array('label'));
        }


        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareLayout() {

        $grandPrarent = get_parent_class(get_parent_class($this));
        return $grandPrarent::_prepareLayout();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('created_time', array(
            'header'=> Mage::helper('sales')->__('Report'),
            'width' => '30px',
            'index' => 'created_time',
            'type'      => 'text',
//            'type'      => 'date',
            'filter' => false,
            'sortable'=>true,
        ));

        if(Mage::getStoreConfig('xpos/general/enabled_cashier') == 1)
        $this->addColumn('cashier_id', array(
            'header' => 'Cashier',
            'index' => 'lastname',
            'type'      => 'options',
            'filter_index' => 'main_table.cashier_id',
            'width' => '40px',
            'renderer'  => 'xpos/adminhtml_report_grid_renderer_cashier',
            'sortable'=>true,
            'options' => Mage::getModel('xpos/user')->getListUser(),
        ));

        if(Mage::getStoreConfig('xpos/general/enable_till') == 1)
        $this->addColumn('till_id', array(
            'header' => 'Till',
            'index' => 'till_name',
            'type'      => 'options',
            'filter_index' => 'main_table.till_id',
            'options' => Mage::getModel('xpos/till')->getListTills(),
            'width' => '20px',
            'sortable'=>true,
        ));

        if(Mage::getStoreConfig('xpos/general/integrate_xmwh_enabled') == 1)
        $this->addColumn('warehouse_id', array(
            'header' => Mage::helper('sales')->__('Warehouse'),
            'index' => 'warehouse_id',
            'width' => '30px',
            'sortable'=>true,

        ));

        $this->addColumn('order_total', array(
            'header' => 'Orders',
            'index' => 'order_total',
            'type'      => 'currency',
            'width' => '15px',
            'sortable'=>true,
        ));

        $this->addColumn('amount_total', array(
            'header' => Mage::helper('sales')->__('Amount'),
            'index' => 'amount_total',
            'type'      => 'currency',
            'currency_code' => $this->getStore()->getCurrentCurrencyCode(),
            'rate'      => $this->getStore()->getBaseCurrency()->getRate($this->getStore()->getCurrentCurrencyCode()),
            'renderer'  => 'adminhtml/sales_order_create_search_grid_renderer_price',
            'width' => '15px',
            'sortable'=>true,
        ));

        return parent::_prepareColumns();
    }

    /**
     * Deprecated since 1.1.7
     */
    public function getRowId($row)
    {
        return $row->getId();
    }

    public function getRowUrl($row)
    {
//        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
//            return $this->getUrl('*/xPos/index', array('order_id' => $row->getId()));
//        }
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/loadBlock', array('block'=>'report_list'));
//        return '#';
    }

    public function getStore()
    {
        return Mage::getSingleton('adminhtml/session_quote')->getStore();
    }


}
