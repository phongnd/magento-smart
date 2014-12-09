<?php
/**
 * Created by PhpStorm.
 * User: Smartor
 * Date: 10/23/14
 * Time: 11:15 AM
 */
class SM_XPos_Model_Resource_Report_Eodreport_Collection extends Mage_Sales_Model_Resource_Report_Collection_Abstract {
    /**
     * Aggregated Data Table
     *
     * @var string
     */
    protected $_aggregationTable = 'xpos/report';

    protected $_mainTable = 'xpos/report';

    protected $_typeDateRange = 'created_at';

    protected $_selectedColumns = array();

    protected $_isTillSelect = true;

    /**
     * Initialize custom resource model
     *
     */
    public function __construct()
    {
        parent::_construct();
        $this->setModel('adminhtml/report_item');
        $this->_resource = Mage::getResourceModel('sales/report')->init($this->_aggregationTable);
        $this->setConnection($this->getResource()->getReadConnection());
    }

    /**
     * Apply order status filter
     *
     * @return Mage_Sales_Model_Resource_Report_Collection_Abstract
     */
    protected function _applyOrderStatusFilter()
    {
        if (is_null($this->_orderStatus)) {
            return $this;
        }
        $orderStatus = $this->_orderStatus;
        if (!is_array($orderStatus)) {
            $orderStatus = array($orderStatus);
        }
        $this->getSelect()->where('status IN(?)', $orderStatus);
        return $this;
    }
    /**
     * Set the date range type - it could be created_at and updated_at
     */

    public function setDateRangeType($type) {
        $this->_typeDateRange = $type;
        return $this;
    }
    /**
     * Apply date range filter
     *
     * @return Mage_Sales_Model_Resource_Report_Collection_Abstract
     */
    protected function _applyDateRangeFilter()
    {
        // Remember that field PERIOD is a DATE(YYYY-MM-DD) in all databases including Oracle

            if ($this->_from !== null) {
                $this->getSelect()->where('created_time  >= ?', $this->_from .' 00:00:00');
            }
            if ($this->_to !== null) {
                $this->getSelect()->where('created_time  <= ?', $this->_to .' 23:59:59');
            }

            return $this;

    }

    // is Till selected
    public function setTillSelected($boolean){
        $this->_isTillSelect = $boolean;

        return $this;
    }


    protected function _initSelect()
    {
        if (!$this->isTotals()) {
            $this->getSelect()->from($this->getResource()->getMainTable());
        } else {
            if($this->_isTillSelect == true){
                $this->_selectedColumns = array(
                    'report_id'                   => 'report_id',
                    'created_time'                => 'created_time',
                    'cashier_id'                  => 'cashier_id',
                    'till_id'                     => 'till_id',
                    'warehouse_id'                => 'warehouse_id',
                    'discount_amount'             => 'SUM(discount_amount)',
                    'order_total'                 => 'SUM(order_total)',
                    'amount_total'                => 'SUM(amount_total)',
                    'transfer_amount'             => 'SUM(transfer_amount)',
                    'tax_amount'                  => 'SUM(tax_amount)',
                    'refund_amount'               => 'SUM(refund_amount)',
                    'cash_system'                 => 'SUM(cash_system)',
                    'cash_count'                  => 'SUM(cash_count)',
                    'check_system'                => 'SUM(check_system)',
                    'check_count'                 => 'SUM(check_count)',
                    'cc_system'                   => 'SUM(cc_system)',
                    'cc_count'                    => 'SUM(cc_count)',
                    'other_system'                => 'SUM(other_system)',
                    'other_count'                 => 'SUM(other_count)',
                );
            }
            else{
                $this->_selectedColumns = array(
                    'report_id'                   => 'report_id',
                    'created_time'                => 'created_time',
                    'cashier_id'                  => 'cashier_id',
                    'till_id'                     => '',
                    'warehouse_id'                => 'warehouse_id',
                    'discount_amount'             => 'SUM(discount_amount)',
                    'order_total'                 => 'SUM(order_total)',
                    'amount_total'                => 'SUM(amount_total)',
                    'transfer_amount'             => 'SUM(transfer_amount)',
                    'tax_amount'                  => 'SUM(tax_amount)',
                    'refund_amount'               => 'SUM(refund_amount)',
                    'cash_system'                 => 'SUM(cash_system)',
                    'cash_count'                  => 'SUM(cash_count)',
                    'check_system'                => 'SUM(check_system)',
                    'check_count'                 => 'SUM(check_count)',
                    'cc_system'                   => 'SUM(cc_system)',
                    'cc_count'                    => 'SUM(cc_count)',
                    'other_system'                => 'SUM(other_system)',
                    'other_count'                 => 'SUM(other_count)',
                );
            }

            $this->getSelect()->from($this->getResource()->getMainTable(),$this->_selectedColumns);
        }

        return $this;
    }
}