<?php
/**
 * Created by PhpStorm.
 * User: Smartor
 * Date: 10/23/14
 * Time: 9:28 AM
 */

class SM_XPos_Block_Report_Eodreport_Eodreport_Grid extends Mage_Adminhtml_Block_Report_Grid_Abstract {

    protected $_columnGroupBy = 'created_time';

    public function __construct()
    {
        parent::__construct();
//        $this->setFilterVisibility(true);
//        $this->setUseAjax(true);
//        $this->setVarNameFilter('product_filter');
        $this->setCountTotals(true);
    }

    public function getResourceCollectionName() {
        return 'xpos/report_eodreport_collection';
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

        $dateRange = 'created_time';

            //till filter
            if($filterData->getData('till_id') != 0){
                $resourceCollection = Mage::getResourceModel($this->getResourceCollectionName())
//                    ->setPeriod($filterData->getData('period_type'))
                    ->setDateRangeType($dateRange)
                    ->setDateRange($filterData->getData('from', null), $filterData->getData('to', null))
                    ->setAggregatedColumns($this->_getAggregatedColumns())
                    ->setTillSelected(true)
                  //  ->addStoreFilter($storeIds)
                    ->addFieldToFilter('till_id',array('eq' => $filterData->getData('till_id')));
            }else{
                $resourceCollection = Mage::getResourceModel($this->getResourceCollectionName())
//                    ->setPeriod($filterData->getData('period_type'))
                    ->setDateRangeType($dateRange)
                    ->setTillSelected(false)
                    //->addStoreFilter($storeIds)
                    ->setDateRange($filterData->getData('from', null), $filterData->getData('to', null))
                    ->setAggregatedColumns($this->_getAggregatedColumns());
            }

//        if($filterData->getData('period_type') != 'custom' && $filterData->getData('period_type') != 'day'){
//            $resourceCollection->isTotals(true);
//        }
            // warehouse filter
            if($filterData->getData('warehouse_id') != 0){
                $resourceCollection
                    ->addFieldToFilter('warehouse_id',array('eq' => $filterData->getData('warehouse_id')));
            }


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

        $this->_forTotalForXPOS();

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
//
//        if($this->getPeriodType()  != 'custom' && $this->getPeriodType()  != 'day')
//        $this->addColumn('period', array(
//            'header'            => Mage::helper('sales')->__('Period'),
//            'index'             => 'created_time',
//            'width'             => '100',
//            'sortable'          => false,
//            'period_type'       => $this->getPeriodType(),
//            'renderer'          => 'adminhtml/report_sales_grid_column_renderer_date',
//            'subtotals_label'   => Mage::helper('sales')->__('Subtotal'),
//            'html_decorators' => array('nobr'),
//        ));

      //  if(($this->getPeriodType()  == 'custom' || $this->getPeriodType()  == 'day') ){
            $this->addColumn('till_id', array(
                'header'        => Mage::helper('sales')->__('Till'),
                'type'          => 'text',
                'index'         => 'till_id',
                'renderer'      => 'xpos/adminhtml_catalog_report_render_till',
                'totals_label'  => Mage::helper('sales')->__('Totals'),
                'group_by'      => 'till_id',
            ));

            $this->addColumn('warehouse_id', array(
                'header'        => Mage::helper('sales')->__('Warehouse'),
                'type'          => 'number',
                'index'         => 'warehouse_id',
                'renderer'      => 'xpos/report_eodreport_eodreport_render_warehouse',
                'totals_label'  => Mage::helper('sales')->__(''),
            ));

            $this->addColumn('cashier_id', array(
                'header'        => Mage::helper('sales')->__('Cashier'),
                'type'          => 'cashier_id',
                'renderer'      => 'xpos/report_eodreport_eodreport_render_cashier',
                'index'         => 'warehouse_id',
                'totals_label'  => Mage::helper('sales')->__(''),
            ));

            $this->addColumn('created_time', array(
                'header'        => Mage::helper('sales')->__('Create time'),
                'type'          => 'datetime',
                'index'         => 'created_time',
                'totals_label'  => Mage::helper('sales')->__(''),
            ));


        $this->addColumn('order_total', array(
            'header'        => Mage::helper('sales')->__('Total Orders'),
            'type'          => 'number',
            'index'         => 'order_total',
            'total'          =>  'sum',
        ));

        $this->addColumn('amount_total', array(
            'header'        => Mage::helper('sales')->__('Total Sum'),
            'type'          => 'currency',
            'index'         => 'amount_total',
            'total'          =>  'sum',
            'currency_code' => $currencyCode,
            'rate'          => $rate,
        ));

        $this->addColumn('tax_amount', array(
            'header'        => Mage::helper('sales')->__('Total Tax'),
            'type'          => 'currency',
            'index'         => 'tax_amount',
            'total'          =>  'sum',
            'currency_code' => $currencyCode,
            'rate'          => $rate,
        ));

        $this->addColumn('transfer_amount', array(
            'header'        => Mage::helper('sales')->__('Transfer'),
            'type'          => 'currency',
            'index'         => 'transfer_amount',
            'currency_code' => $currencyCode,
            'rate'          => $rate,
            'total'          =>  'sum',
        ));

        $this->addColumn('discount_amount', array(
            'header'        => Mage::helper('sales')->__('Total Discount'),
            'type'          => 'currency',
            'index'         => 'discount_amount',
            'total'          =>  'sum',
            'currency_code' => $currencyCode,
            'rate'          => $rate,
        ));

        $this->addColumn('cash_system', array(
            'header'        => Mage::helper('sales')->__('Cash System'),
            'type'          => 'currency',
            'index'         => 'cash_system',
            'total'          =>  'sum',
            'currency_code' => $currencyCode,
            'rate'          => $rate,
        ));

        $this->addColumn('cash_count', array(
            'header'        => Mage::helper('sales')->__('Cash Count'),
            'type'          => 'currency',
            'index'         => 'cash_count',
            'total'          =>  'sum',
            'currency_code' => $currencyCode,
            'rate'          => $rate,
        ));

        $this->addColumn('cc_system', array(
            'header'        => Mage::helper('sales')->__('Credit Card System'),
            'type'          => 'currency',
            'index'         => 'cc_system',
            'total'          =>  'sum',
            'currency_code' => $currencyCode,
            'rate'          => $rate,
        ));
        $this->addColumn('cc_count', array(
            'header'        => Mage::helper('sales')->__('Credit Card Count'),
            'type'          => 'currency',
            'index'         => 'cc_count',
            'total'          =>  'sum',
            'currency_code' => $currencyCode,
            'rate'          => $rate,
        ));

        $this->addColumn('check_system', array(
            'header'        => Mage::helper('sales')->__('Check System'),
            'type'          => 'currency',
            'index'         => 'check_system',
            'total'          =>  'sum',
            'currency_code' => $currencyCode,
            'rate'          => $rate,
        ));

        $this->addColumn('check_count', array(
            'header'        => Mage::helper('sales')->__('Check Count'),
            'type'          => 'currency',
            'index'         => 'check_count',
            'total'          =>  'sum',
            'currency_code' => $currencyCode,
            'rate'          => $rate,
        ));

        $this->addColumn('other_system', array(
            'header'        => Mage::helper('sales')->__('Other System'),
            'type'          => 'currency',
            'index'         => 'other_system',
            'total'          =>  'sum',
            'currency_code' => $currencyCode,
            'rate'          => $rate,
        ));

        $this->addColumn('other_count', array(
            'header'        => Mage::helper('sales')->__('Other Count'),
            'type'          => 'currency',
            'index'         => 'other_count',
            'total'          =>  'sum',
            'currency_code' => $currencyCode,
            'rate'          => $rate,
        ));



        $this->addExportType('*/*/exportEodCsv', Mage::helper('adminhtml')->__('CSV'));
        $this->addExportType('*/*/exportEodExcel', Mage::helper('adminhtml')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    /**
     * Retrieve Totals row array for Export
     *
     * @return array
     */
    protected function _getExportTotals()
    {
        $this->_forTotalForXPOS();
        $totals = $this->getTotals();
        $row    = array();
        foreach ($this->_columns as $column) {
            if (!$column->getIsSystem()) {
                $row[] = ($column->hasTotalsLabel()) ? $column->getTotalsLabel() : $column->getRowFieldExport($totals);
            }
        }
        return $row;
    }

    /**
     * @param $filterData
     * @param $dateRange
     */
    protected function _forTotalForXPOS()
    {
        $filterData = $this->getFilterData();
        $dateRange = 'created_time';
        if ($this->getCountTotals()) {
            if ($filterData->getData('till_id') != 0) {
                $totalsCollection = Mage::getResourceModel($this->getResourceCollectionName())
//                    ->setPeriod($filterData->getData('period_type'))
                    ->setDateRangeType($dateRange)
                    ->setDateRange($filterData->getData('from', null), $filterData->getData('to', null))
                    ->setAggregatedColumns($this->_getAggregatedColumns())
                    ->addFieldToFilter('till_id', array('eq' => $filterData->getData('till_id')))
                    ->isTotals(true);
            } else {
                $totalsCollection = Mage::getResourceModel($this->getResourceCollectionName())
//                    ->setPeriod($filterData->getData('period_type'))
                    ->setDateRangeType($dateRange)
                    ->setDateRange($filterData->getData('from', null), $filterData->getData('to', null))
                    ->setAggregatedColumns($this->_getAggregatedColumns())
                    ->isTotals(true);
            }

            $this->_addOrderStatusFilter($totalsCollection, $filterData);
            $this->_addCustomFilter($totalsCollection, $filterData);

            foreach ($totalsCollection as $item) {
                $this->setTotals($item);
                break;
            }
        }
    }
}