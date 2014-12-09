<?php

class SM_XPos_Block_Adminhtml_Index_Transaction_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_headersVisibility = true;
    protected $_pagerVisibility = true;

    public function __construct($attributes=array())
    {
        /**
         * Override from grandparent to prevent setTemplate from parent
        */

        //$this->setTemplate('sm/xpos/widget/grid.phtml');
        parent::__construct();
        $this->setRowClickCallback('openGridRow');
        $this->_emptyText = Mage::helper('adminhtml')->__('No records found.');
        $this->setId('sales_order_create_transaction_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(false);

    }


    protected function _prepareCollection()
    {
        $till_id = $this->getRequest()->getParam('till_id');
        if($till_id == ''){
            $till_id = Mage::getSingleton('adminhtml/session')->getTillId();
        }

        $collection = Mage::getSingleton('xpos/transaction')->getCollection()
                    ->addFieldToFilter('transac_flag',array('eq'=>'1'));

        if($till_id != 'false'){
            Mage::getSingleton('adminhtml/session')->setTillId($till_id);
            $collection->addFieldToFilter('till_id', array('eq' => $till_id));
        }
        $select = $collection->getSelect();
        /*$select->join(array('A' => $collection->getTable('admin/user')),'`main_table`.`user_id` = `A`.`user_id`')
            ->group('transaction_id');*/
        $select->join(array('A' => $collection->getTable('admin/user')),'`main_table`.`user_id` = `A`.`user_id`');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareLayout() {

        $grandPrarent = get_parent_class(get_parent_class($this));
        return $grandPrarent::_prepareLayout();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('date', array(
            'header'=> Mage::helper('sales')->__('Date'),
            'width' => '30px',
            'index' => 'created_time',
            /*'type'      => 'date',
            'filter_time' => false,*/
            'sortable'=>true,
        ));

        $this->addColumn('cash_in', array(
            'header' => Mage::helper('sales')->__('In'),
            'index' => 'cash_in',
            'type'      => 'currency',
            'currency_code' => $this->getStore()->getCurrentCurrencyCode(),
            'rate'      => $this->getStore()->getBaseCurrency()->getRate($this->getStore()->getCurrentCurrencyCode()),
            'renderer'  => 'adminhtml/sales_order_create_search_grid_renderer_price',
            'width' => '20px',
            'sortable'=>true,
        ));

        $this->addColumn('cash_out', array(
            'header' => Mage::helper('sales')->__('Out'),
            'index' => 'cash_out',
            'type'      => 'currency',
            'currency_code' => $this->getStore()->getCurrentCurrencyCode(),
            'rate'      => $this->getStore()->getBaseCurrency()->getRate($this->getStore()->getCurrentCurrencyCode()),
            'renderer'  => 'adminhtml/sales_order_create_search_grid_renderer_price',
            'width' => '20px',
            'sortable'=>true,
        ));
        $this->addColumn('order_id', array(
            'header' => Mage::helper('sales')->__('Order ID'),
            'index' => 'order_id',
            'width' => '20px',
            'sortable'=>true,

        ));

        $enbale_till = Mage::getStoreConfig('xpos/general/enable_till');
        if($enbale_till == 0) {

            $this->addColumn('previous_balance', array(
                'header' => Mage::helper('sales')->__('Previous Bal.'),
                'index' => 'previous_balance',
                'type'      => 'currency',
                'currency_code' => $this->getStore()->getCurrentCurrencyCode(),
                'rate'      => $this->getStore()->getBaseCurrency()->getRate($this->getStore()->getCurrentCurrencyCode()),
                'renderer'  => 'adminhtml/sales_order_create_search_grid_renderer_price',
                'width' => '30px',
            ));

            $this->addColumn('current_balance', array(
                'header' => Mage::helper('sales')->__('Current Bal.'),
                'index' => 'current_balance',
                'type'      => 'currency',
                'currency_code' => $this->getStore()->getCurrentCurrencyCode(),
                'rate'      => $this->getStore()->getBaseCurrency()->getRate($this->getStore()->getCurrentCurrencyCode()),
                'renderer'  => 'adminhtml/sales_order_create_search_grid_renderer_price',
                'width' => '30px',
            ));
        }

        $this->addColumn('username', array(
            'header' => Mage::helper('sales')->__('User'),
            'index' => 'username',
            'type'=> 'text',
        ));

        $this->addColumn('comment', array(
            'header' => Mage::helper('sales')->__('Note'),
            'index' => 'comment',
            'type'=> 'text',
            'width' => '30px',
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
        return $this->getUrl('*/*/loadBlock', array('block'=>'transaction_grid'));
//        return '#';
    }

    public function getStore()
    {
        return Mage::getSingleton('adminhtml/session_quote')->getStore();
    }


}
