<?php
class SM_XPos_Block_Adminhtml_Till_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('tillGrid');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }
    
    protected function _prepareCollection() {
        $collection = Mage::getModel('xpos/till')->getCollection();
        if(Mage::getStoreConfig('xpos/general/integrate_xmwh_enabled')==1){
            $collection->getSelect()->joinLeft(
                array('warehouseTable'=>$collection->getTable('xwarehouse/warehouse')),
                '`main_table`.`warehouse_id` = `warehouseTable`.`warehouse_id`',array('label')
            );
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('till_id', array(
            'header' => Mage::helper('xpos')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'till_id',
        ));

        $this->addColumn('till_name', array(
            'header' => Mage::helper('xpos')->__('Till name'),
            'align' => 'left',
            'index' => 'till_name',
        ));

        $this->addColumn('is_active', array(
            'header' => Mage::helper('xpos')->__('Active'),
            'align' => 'left',
            'index' => 'is_active',
            'renderer' => 'xpos/adminhtml_till_render_isactive'
        ));

        $this->addColumn('warehouse_name', array(
            'header' => Mage::helper('xpos')->__('Warehouse'),
            'align' => 'left',
            'index' => 'label',
            'renderer' => 'xpos/adminhtml_till_render_warehouse'
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getTillId()));
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('till_id');
        $this->getMassactionBlock()->setFormFieldName('till');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('xpos')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('xpos')->__('Are you sure?')
        ));

        return $this;
    }

}