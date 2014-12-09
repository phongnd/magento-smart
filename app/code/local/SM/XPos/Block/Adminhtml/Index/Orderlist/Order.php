<?php

class SM_XPos_Block_Adminhtml_Index_Orderlist_Order extends Mage_Adminhtml_Block_Widget_Grid
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
        $collection = Mage::getResourceModel('sales/order_grid_collection');
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

        $user_limit = $this->getRequest()->getParam('user_limited');
        if($user_limit != NULL) Mage::getSingleton('core/session')->setUserLimit($user_limit);
        $limit  = Mage::getSingleton('core/session')->getUserLimit();
        if($limit==1)
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
        return $this->getUrl('*/*/loadBlock', array('block'=>'order_grid'));
    }

}
