<?php


class SM_XPos_Block_Adminhtml_Cashier_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('cashierGrid');
        $this->setDefaultSort('xpos_user_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }
    
    protected function _prepareCollection() {
        $collection = Mage::getModel('xpos/user')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('xpos_user_id', array(
            'header' => Mage::helper('xpos')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'xpos_user_id',
        ));

        $this->addColumn('username', array(
            'header' => Mage::helper('xpos')->__('Username'),
            'align' => 'left',
            'index' => 'username',
        ));

        $this->addColumn('firstname', array(
            'header' => Mage::helper('xpos')->__('FirstName'),
            'align' => 'left',
            'index' => 'firstname',
        ));

        $this->addColumn('lastname', array(
            'header' => Mage::helper('xpos')->__('LastName'),
            'align' => 'left',
            'index' => 'lastname',
        ));

        $this->addColumn('created_time', array(
            'header' => Mage::helper('xpos')->__('CreatedTime'),
            'align' => 'left',
            'index' => 'created_time',
        ));

        $this->addColumn('modified_time ', array(
            'header' => Mage::helper('xpos')->__('ModifiedTime '),
            'align' => 'left',
            'index' => 'modified_time',
        ));

        $this->addColumn('is_active', array(
            'header' => Mage::helper('xpos')->__('Active'),
            'align' => 'left',
            'index' => 'is_active',
            'renderer' => 'xpos/adminhtml_cashier_render_isactive'
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getxposUserId()));
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('xpos_user_id');
        $this->getMassactionBlock()->setFormFieldName('cashier');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('xpos')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('xpos')->__('Are you sure?')
        ));

        return $this;
    }

}