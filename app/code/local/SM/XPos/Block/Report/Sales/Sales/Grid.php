<?php
/**
 * Author: HieuNT
 * Email: hieunt@smartosc.com
 */

class SM_XPos_Block_Report_Sales_Sales_Grid extends Mage_Adminhtml_Block_Report_Grid_Abstract {

    protected $_columnGroupBy = 'entity_id';

    public function __construct()
    {
        parent::__construct();
//        $this->setFilterVisibility(true);
//        $this->setUseAjax(true);
//        $this->setVarNameFilter('product_filter');
        $this->setCountTotals(true);
    }

    public function getResourceCollectionName() {
        return 'xpos/report_order_collection';
    }

    protected function _prepareCollection() {
        $filterData = $this->getFilterData();

        if ($filterData->getData('from') == null || $filterData->getData('to') == null) {
            $this->setCountTotals(false);
            $this->setCountSubTotals(false);
            return parent::_prepareCollection();
        }

        $storeIds = $this->_getStoreIds();

        $orderStatuses = $filterData->getData('order_statuses');
        if (is_array($orderStatuses)) {
            if (count($orderStatuses) == 1 && strpos($orderStatuses[0],',')!== false) {
                $filterData->setData('order_statuses', explode(',',$orderStatuses[0]));
            }
        }
        if ($this->getFilterData()->getData('report_type') == 'updated_at_order') {
            $dateRange = 'updated_at';
        } else {
            $dateRange = 'created_at';
        }

        if($filterData->getData('till_id') != 0){
            $resourceCollection = Mage::getResourceModel($this->getResourceCollectionName())
//                ->setPeriod($filterData->getData('period_type'))
                ->setDateRangeType($dateRange)
                ->setDateRange($filterData->getData('from', null), $filterData->getData('to', null))
                ->addStoreFilter($storeIds)
                ->setAggregatedColumns($this->_getAggregatedColumns())
                ->addFieldToFilter('till_id',array('eq' => $filterData->getData('till_id')));
        }else{
            $resourceCollection = Mage::getResourceModel($this->getResourceCollectionName())
//                ->setPeriod($filterData->getData('period_type'))
                ->setDateRangeType($dateRange)
                ->setDateRange($filterData->getData('from', null), $filterData->getData('to', null))
                ->addStoreFilter($storeIds)
                ->setAggregatedColumns($this->_getAggregatedColumns());
        }

        // warehouse filter
        if($filterData->getData('warehouse_id') != 0){
            $resourceCollection
                ->addFieldToFilter('warehouse_id',array('eq' => $filterData->getData('warehouse_id')));
            $resourceCollection->getSelect()->join(array('pm' => $resourceCollection->getTable('xwarehouse/warehouse')),'pm.warehouse_id = '.Mage::getSingleton('core/resource')->getTableName('sales_flat_order').'.warehouse_id');
        }

        //Filter per options: oder, till, warehouse, cashier
        $type_view = $filterData->getData('report_by_type');
        switch($type_view){
            case 'orderlist':

                break;
            case 'till':
                $resourceCollection->getSelect()->group('till_id');
                break;
            case 'cashier':
                $resourceCollection->getSelect()->group('xpos_user_id');
                break;
            case 'warehouse':
                $resourceCollection->getSelect()->group('warehouse_id');
                break;
            case 'payment':
                $resourceCollection->getSelect()->group('method');
                break;
            default: break;
        }

        //Order from xpos filter
        if($filterData->getData('xpos_only') == 1){
            $resourceCollection ->addFieldToFilter('xpos',array('eq' => 1));
        }

//        if(($filterData->getData('period_type') != 'custom' && $filterData->getData('period_type') != 'day' )|| $filterData->getData('report_by_type') != 'orderlist'){
//            $resourceCollection->setTotalPerSelected($filterData->getData('report_by_type'));
//            $resourceCollection->isTotals(true);
//        }
        if($filterData->getData('report_by_type') != 'orderlist'){
            $resourceCollection->setTotalPerSelected($filterData->getData('report_by_type'));
            $resourceCollection->isTotals(true);
        }

        $resourceCollection->getSelect()->join(array('pm' => $resourceCollection->getTable('sales/order_payment')),'pm.parent_id = '.Mage::getSingleton('core/resource')->getTableName('sales_flat_order').'.entity_id');


        $this->_addOrderStatusFilter($resourceCollection, $filterData);
        $this->_addCustomFilter($resourceCollection, $filterData);

        if ($this->_isExport) {
            $this->setCollection($resourceCollection);
            return $this;
        }


        /*if ($filterData->getData('show_empty_rows', false)) {
            Mage::helper('reports')->prepareIntervalsCollection(
                $this->getCollection(),
                $filterData->getData('from', null),
                $filterData->getData('to', null),
                $filterData->getData('period_type')
            );
        }*/

        if ($this->getCountSubTotals()) {
            $this->getSubTotals();
        }

        if ($this->getCountTotals()) {


            if($filterData->getData('till_id') != 0){
                $totalsCollection = Mage::getResourceModel($this->getResourceCollectionName())
//                    ->setPeriod($filterData->getData('period_type'))
                    ->setDateRangeType($dateRange)
                    ->setDateRange($filterData->getData('from', null), $filterData->getData('to', null))
                    ->addStoreFilter($storeIds)
                    ->setAggregatedColumns($this->_getAggregatedColumns())
                    ->addFieldToFilter('xpos',array('eq' => 1))
                    ->addFieldToFilter('till_id',array('eq' => $filterData->getData('till_id')))
                    ->isTotals(true);
            }else{
                $totalsCollection = Mage::getResourceModel($this->getResourceCollectionName())
//                    ->setPeriod($filterData->getData('period_type'))
                    ->setDateRangeType($dateRange)
                    ->setDateRange($filterData->getData('from', null), $filterData->getData('to', null))
                    ->addStoreFilter($storeIds)
                    ->setAggregatedColumns($this->_getAggregatedColumns())
                    ->addFieldToFilter('xpos',array('eq' => 1))
                    ->isTotals(true);
            }


            // warehouse filter
            if($filterData->getData('warehouse_id') != 0){
                $totalsCollection
                    ->addFieldToFilter('warehouse_id',array('eq' => $filterData->getData('warehouse_id')));
                $totalsCollection->getSelect()->join(array('pm' => $totalsCollection->getTable('xwarehouse/warehouse')),'pm.warehouse_id = '.Mage::getSingleton('core/resource')->getTableName('sales_flat_order').'.warehouse_id');
            }

            $this->_addOrderStatusFilter($totalsCollection, $filterData);
            $this->_addCustomFilter($totalsCollection, $filterData);

            foreach ($totalsCollection as $item) {
                $this->setTotals($item);
                break;
            }
        }

        $this->getCollection()->setColumnGroupBy($this->_columnGroupBy);
        $this->getCollection()->setResourceCollection($resourceCollection);

        /*$grandParent = get_parent_class(get_parent_class($this));
        return $grandParent::_prepareCollection();*/
        return $this;
    }

    protected function _prepareColumns()
    {
        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }
        $currencyCode = $this->getCurrentCurrencyCode();
        $rate = $this->getRate($currencyCode);


        if($this->getFilterData()->getData('report_by_type')  == 'orderlist')
        {
           // if($this->getPeriodType()  == 'custom' || $this->getPeriodType()  == 'day')
            $this->addColumn('increment_id', array(
                'header'        => Mage::helper('sales')->__('Order Id'),
                'type'          => 'number',
                'index'         => 'increment_id',
                'totals_label'  => Mage::helper('sales')->__('Totals'),
            ));
        }


        if ($this->getFilterData()->getData('report_type') == 'updated_at_order') {
            $this->addColumn('updated_at', array(
                'header'        => Mage::helper('sales')->__('Updated at'),
                'type' => 'datetime',
                'index'         => 'updated_at',
                'totals_label'  => Mage::helper('sales')->__(''),
            ));
        } else {
            if($this->getFilterData()->getData('report_by_type') == 'orderlist')
            $this->addColumn('created_at', array(
                'header'        => Mage::helper('sales')->__('Created at'),
                'type' => 'datetime',
                'index'         => 'created_at',
                'totals_label'  => Mage::helper('sales')->__(''),
            ));
        }

        $this->addColumn('subtotal', array(
            'header'        => Mage::helper('sales')->__('Amount Ex tax'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'subtotal',
            'rate'          => $rate,
            'total'          =>  'sum',
        ));

        $this->addColumn('tax_amount', array(
            'header'        => Mage::helper('sales')->__('Tax Amount'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'tax_amount',
            'rate'          => $rate,
            'total'         => 'sum',
        ));

        $this->addColumn('grand_total', array(
            'header'        => Mage::helper('sales')->__('Amount'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'grand_total',
            'rate'          => $rate,
            'total'     => 'sum',
        ));

        $this->addColumn('discount_amount', array(
            'header'        => Mage::helper('sales')->__('Discount'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'discount_amount',
            'rate'          => $rate,
            'total'         =>  'sum',
        ));

        $this->addColumn('total_paid', array(
            'header'        => Mage::helper('sales')->__('Paid'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'total_paid',
            'rate'          => $rate,
            'total'         =>  'sum',
        ));

        $this->addColumn('total_refunded', array(
            'header'        => Mage::helper('sales')->__('Refunded'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'total_refunded',
            'rate'          => $rate,
            'total'         =>  'sum',
        ));

        $this->addColumn('total_change', array(
            'header'        => Mage::helper('sales')->__('Change'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'total_change',
            'align'         => 'right',
            'renderer'      => 'xpos/adminhtml_catalog_report_render_change',
            'rate'          => $rate,
            'total'         =>  'sum',
        ));

//        $this->addColumn('shipping_amount', array(
//            'header'        => Mage::helper('sales')->__('Shipping Amount'),
//            'type'          => 'currency',
//            'currency_code' => $currencyCode,
//            'index'         => 'shipping_amount',
//            'rate'          => $rate,
//            'total'         =>  'sum',
//        ));
        if($this->getFilterData()->getData('report_by_type')=='cashier' || $this->getFilterData()->getData('report_by_type')=='orderlist')
        $this->addColumn('xpos_user_id', array(
            'header'        => Mage::helper('sales')->__('Sales Person'),
            'type'          => 'text',
            'index'         => 'xpos_user_id',
            'renderer'      => 'xpos/adminhtml_catalog_report_render_user',
            'totals_label'  => Mage::helper('sales')->__(''),
        ));
        if($this->getFilterData()->getData('report_by_type')=='till' || $this->getFilterData()->getData('report_by_type')=='orderlist')
        $this->addColumn('till_id', array(
            'header'        => Mage::helper('sales')->__('Till'),
            'type'          => 'text',
            'index'         => 'till_id',
            'renderer'      => 'xpos/adminhtml_catalog_report_render_till',
            'totals_label'  => Mage::helper('sales')->__(''),
            'group_by'      => 'till_id',
        ));
        if($this->getFilterData()->getData('report_by_type')=='orderlist')
        $this->addColumn('shipping_method', array(
            'header'        => Mage::helper('sales')->__('Shipping Method'),
            'type'          => 'text',
            'index'         => 'shipping_method',
            'renderer'      => 'xpos/adminhtml_catalog_report_render_shipping',
            'totals_label'  => Mage::helper('sales')->__(''),
        ));
        if($this->getFilterData()->getData('report_by_type')=='payment' || $this->getFilterData()->getData('report_by_type')=='orderlist')
        $this->addColumn('method', array(
            'header'        => Mage::helper('sales')->__('Payment Method'),
            'type'          => 'text',
            'index'         => 'method',
            'renderer'      => 'xpos/adminhtml_catalog_report_render_method',
            'totals_label'  => Mage::helper('sales')->__(''),

        ));

        if($this->getFilterData()->getData('report_by_type')=='warehouse' || $this->getFilterData()->getData('report_by_type')=='orderlist')
            $this->addColumn('warehouse_id', array(
                'header'        => Mage::helper('sales')->__('Warehouse'),
                'type'          => 'text',
                'index'         => 'warehouse_id',
                'renderer'      => 'xpos/adminhtml_catalog_report_render_warehouse',
                'totals_label'  => Mage::helper('sales')->__(''),

            ));

        if($this->getFilterData()->getData('report_by_type')=='orderlist')
        $this->addColumn('store_id', array(
            'header'        => Mage::helper('sales')->__('Store'),
            'type'          => 'text',
            'index'         => 'store_id',
            'renderer'      => 'xpos/adminhtml_catalog_report_render_store',
            'totals_label'  => Mage::helper('sales')->__(''),
        ));

        $this->addExportType('*/*/exportPosSalesCsv', Mage::helper('adminhtml')->__('CSV'));
        $this->addExportType('*/*/exportPosSalesExcel', Mage::helper('adminhtml')->__('Excel XML'));

        return parent::_prepareColumns();
    }
}