<?php

require_once(BP . DS . 'app' . DS . 'code' . DS . 'core' . DS . 'Mage' . DS . 'Adminhtml' . DS . 'controllers' . DS . 'Report' . DS . 'SalesController.php');

class SM_XPos_Adminhtml_ReportController extends Mage_Adminhtml_Report_SalesController {
    public function xposAction() {
        $this->_title($this->__('Reports'))->_title($this->__('Sales'))->_title($this->__('Pos Sales'));

        $this->_showLastExecutionTime(Mage_Reports_Model_Flag::REPORT_ORDER_FLAG_CODE, 'sales');

        $this->_initAction()
            ->_setActiveMenu('report/sales/sales')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('POS Sales Report'), Mage::helper('adminhtml')->__('POS Sales Report'));

        $gridBlock = $this->getLayout()->getBlock('report_sales_sales.grid');
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction(array(
            $gridBlock,
            $filterFormBlock
        ));

        $this->renderLayout();
    }

    public function eodreportAction(){
        $this->_title($this->__('Reports'))->_title($this->__('Sales'))->_title($this->__('End of day'));

        $this->_showLastExecutionTime(Mage_Reports_Model_Flag::REPORT_ORDER_FLAG_CODE, 'sales');

        $this->_initAction()
            ->_setActiveMenu('report/sales/sales')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('POS Sales Report'), Mage::helper('adminhtml')->__('POS Sales Report'));

        $gridBlock = $this->getLayout()->getBlock('report_eodreport_eodreport.grid');
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction(array(
            $gridBlock,
            $filterFormBlock
        ));

        $this->renderLayout();
    }

    public function exportPosSalesCsvAction()
    {
        $fileName   = 'sales.csv';
        $grid       = $this->getLayout()->createBlock('xpos/report_sales_sales_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     * Export sales report grid to Excel XML format
     */
    public function exportPosSalesExcelAction()
    {
        $fileName   = 'sales.xml';
        $grid       = $this->getLayout()->createBlock('xpos/report_sales_sales_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    //Export end of day report Csv
    public function exportEodCsvAction()
    {
        $fileName   = 'sales.csv';
        $grid       = $this->getLayout()->createBlock('xpos/report_eodreport_eodreport_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }
    //Export end of day report Excel
    public function exportEodExcelAction()
    {
        $fileName   = 'sales.xml';
        $grid       = $this->getLayout()->createBlock('xpos/report_eodreport_eodreport_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    public function saveZReportAction(){
        $data = Mage::getSingleton('adminhtml/session')->getPaymentInfo();
        $refund_amount = $data['other_payment']['total_refund'];

        $order_total = $this->getRequest()->getParam('order_total');
        $amount_total = $this->getRequest()->getParam('amount_total');
        $transfer_amount = $this->getRequest()->getParam('transfer_amount');
        $tax_amount = $this->getRequest()->getParam('tax_amount');
        $discount_amount = $this->getRequest()->getParam('discount_amount');
        $cash_system = $this->getRequest()->getParam('cash_system');
        $cash_count = $this->getRequest()->getParam('cash_count');
        $check_system = $this->getRequest()->getParam('check_system');
        $check_count = $this->getRequest()->getParam('check_count');
        $cc_system = $this->getRequest()->getParam('cc_system');
        $cc_count = $this->getRequest()->getParam('cc_count');
        $other_system = $this->getRequest()->getParam('other_system');
        $other_count = $this->getRequest()->getParam('other_count');
//        $create_time = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
        $create_time = date("Y-m-d H:i:s",time());

        if(Mage::getStoreConfig('xpos/general/enable_till') == 1){
            $till_id = $this->getRequest()->getParam('till_id');
        }
        else{
            $till_id = 0;
        }

        if(Mage::getStoreConfig('xpos/general/integrate_xmwh_enabled')){
            $warehouse_id = Mage::getSingleton('admin/session')->getWarehouseId();
            Mage::getSingleton('adminhtml/session')->setWarehouseReport($warehouse_id);
        }
        else $warehouse_id =0;
        if(Mage::getStoreConfig('xpos/general/enabled_cashier') == 1){
            $cashier_id =$this->getRequest()->getParam('cashier_id');
            Mage::getSingleton('adminhtml/session')->setCashierReport($cashier_id);
        }
        else{
            $cashier_id = 0 ;
        }

        $data = array();
        $data['refund_amount'] = $refund_amount;

        $data['created_time'] = $create_time;
        $data['till_id'] = $till_id;
        $data['cashier_id'] = $cashier_id;
        $data['warehouse_id'] = $warehouse_id;
        $data['order_total'] = $order_total;
        $data['amount_total'] = $amount_total;
        $data['transfer_amount'] = $transfer_amount;
        $data['tax_amount'] = $tax_amount;
        $data['discount_amount'] = $discount_amount;
        $data['cash_system'] = $cash_system;
        $data['cash_count'] = $cash_count;
        $data['check_system'] = $check_system;
        $data['check_count'] = $check_count;
        $data['cc_system'] = $cc_system;
        $data['cc_count'] = $cc_count;
        $data['other_system'] = $other_system;
        $data['other_count'] = $other_count;

        $model = Mage::getModel('xpos/report');
        $model->setData($data);
        try{
            $model->save();
        }catch (Exception $e) {
            echo "<pre>";
            var_dump( $e->getMessage() );
          //  Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            return;
        }
    }

    public function printZreportAction(){
        $this->_title("Z - Report");
        $this->loadLayout();
        $this->renderLayout();
    }

    public function setTransacFlagAction(){
        $till_id = $this->getRequest()->getParam('till_id');

        if($till_id == 'NULL'){
            $transac_collection = Mage::getModel('xpos/transaction')->getCollection()
                ->addFieldToFilter('till_id',array('eq'=>0))
                ->addFieldToFilter('transac_flag',array('eq'=>'1'));
        }
        else{
            $transac_collection = Mage::getModel('xpos/transaction')->getCollection()
                ->addFieldToFilter('till_id',array('eq'=>$till_id))
                ->addFieldToFilter('transac_flag',array('eq'=>'1'));
        }

        foreach($transac_collection as $transac){
            $transac->setData('transac_flag','0');
            $transac->save();
        }

        $result = array();

        $this->getResponse()->setBody(json_encode($result));
    }
}