<?php

class SM_XPos_Block_Adminhtml_Index_Orderlist_Saved extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_filterVisibility = false;
    protected $_headersVisibility = true;
    protected $_pagerVisibility = false;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('sm/xpos/widget/grid.phtml');
        $this->setId('sales_order_create_order_grid');
        $this->setRowClickCallback('order.selectOrder.bind(order)');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setPagerVisibility(true);
        $this->setFilterVisibility(true);
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);

    }

    protected function _prepareCollection()
    {
        //$collection = Mage::getResourceModel('sales/order_grid_collection');
        $collection = Mage::getResourceModel('sales/order_collection');
        $collection->addFieldToFilter('status', 'pending');
        $collection->addFieldToFilter('xpos', 1);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('real_order_id', array(
            'header'=> Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'main_table.increment_id',
            'renderer'=> 'xpos/adminhtml_index_orderlist_renderer_increment',
        ));
 
        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'type'  => 'text',
            /*'index' => 'billing_name',*/
            'renderer' => 'xpos/adminhtml_index_orderlist_renderer_customer',
            /*'filter_index' => 'main_table.billing_name',*/
        ));
        
        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'filter_index' => 'main_table.created_at',
            'filter' => false,
            'width' => '100px',
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('Grand Total'),
            'index' => 'grand_total',
            'type' => 'price',
            'filter' => false,
            'width' => '120px',
        ));

        $this->addColumn('option_name', array(
            'header' => Mage::helper('sales')->__('Quick action'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'filter'    => false,
            'renderer' => 'xpos/adminhtml_index_orderlist_renderer_actionsaved',
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
        //if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
        //    return $this->getUrl('*/xPos/index', array('order_id' => $row->getId()));
        //}
        return '';
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/loadBlock', array('block'=>'order_grid'));
    }

}
