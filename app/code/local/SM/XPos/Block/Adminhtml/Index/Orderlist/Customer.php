<?php

class SM_XPos_Block_Adminhtml_Index_Orderlist_Customer extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_filterVisibility = false;
    protected $_headersVisibility = true;
    protected $_pagerVisibility = false;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('sm/xpos/widget/grid.phtml');
        $this->setId('sales_order_customer_grid');
        //$this->setRowClickCallback('order.selectOrder.bind(order)');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setPagerVisibility(true);
        $this->setFilterVisibility(true);
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
        $customer_id = $this->getRequest()->getParam('customer_id');
        $collection = Mage::getResourceModel('sales/order_grid_collection');
        $collection->addFieldToFilter('customer_id', array('eq' => $customer_id));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', array(
            'header'=> Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'increment_id',
            'renderer'=> 'xpos/adminhtml_index_orderlist_renderer_increment',
        ));
 
        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
            'filter_index' => 'main_table.billing_name',
        ));
        
        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'filter_index' => 'main_table.created_at',
            'filter' => false,
            'width' => '100px',
        ));        

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'filter_index' => 'main_table.status',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

        $this->addColumn('option_name', array(
            'header' => Mage::helper('sales')->__('Quick action'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'filter'    => false,
            'renderer' => 'xpos/adminhtml_index_orderlist_renderer_action',
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
        return $this->getUrl('*/*/loadBlock', array('block'=>'order_customer_grid', 'customer_id' => $this->getRequest()->getParam('customer_id')));
    }

}
