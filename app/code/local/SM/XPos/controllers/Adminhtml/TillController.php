<?php

require_once(BP . DS . 'app' . DS . 'code' . DS . 'core' . DS . 'Mage' . DS . 'Adminhtml' . DS . 'controllers' . DS . 'Report' . DS . 'SalesController.php');

class SM_XPos_Adminhtml_TillController extends Mage_Adminhtml_Controller_Action
{

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/sales/xpos/till');
    }

    public function indexAction()
    {
        $this->_title("Manager Tills - XPos");
        $this->loadLayout();
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('xpos/till')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);

            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('till_data', $model);

            $this->loadLayout();

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Till Manager'), Mage::helper('adminhtml')->__('Till Manager'));

            $this->_addContent($this->getLayout()->createBlock('xpos/adminhtml_till_edit'))
                ->_addLeft($this->getLayout()->createBlock('xpos/adminhtml_till_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('xpos')->__('Till does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $till_id = $this->getRequest()->getParam('id');
            $tillname = $data['till_name'];
            $warehouseId = $data['warehouse_id'];
            if (isset($tillname)) {
                $till = Mage::getModel('xpos/till')->getCollection();
                foreach ($till as $row) {
                    if ($tillname == $row->getTillName() && $row->getTillId() != $till_id) {
                        Mage::getSingleton('adminhtml/session')->addError($this->__('Duplicate till name, please use another name for till'));
                        Mage::getSingleton('adminhtml/session')->setFormData($data);
                        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                        return;
                    }
                }
            }
            $data['is_active'] = isset($data['is_active']) ? 1 : 0;
            $model = Mage::getModel('xpos/till');
            $model->setData($data)->setId($this->getRequest()->getParam('id'));

            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('xpos')->__('Till was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('xpos')->__('Unable to find till to save'));
        $this->_redirect('*/*/');
    }

    public function setWarehouseAction()
    {
        $warehouse_id = $this->getRequest()->getParam('warehouse_id');
        //Mage::register("warehouse_id",$warehouse_id);
        //$this->_getSession()->setWarehouseId((int) $warehouse_id);
        Mage::getSingleton('adminhtml/session')->setWarehouseId($warehouse_id);
        $collection = Mage::getModel('xpos/till')->getCollection()
            ->addFieldToFilter('warehouse_id', $warehouse_id)
            ->addFieldToFilter('is_active', 1);

        $data_till = $collection->getData();

        $html_till = '';

        if (count($data_till) > 0) {
            foreach ($data_till as $till) {
            $html_till .= '<li><a onclick="setTill(\''.$till['till_id'].'\',\''. $till['till_name'].'\')" href="javascript:">'.$till['till_name'].'</a></li>';
            }
        } else {
            $html_till = '<li><span class="no-till no-hover">Please add, or enable till for this category</span></li>';
        }


        echo $html_till;
    }

    public function loadWarehouseAction()
    {
        Mage::getSingleton('admin/session')->unsWarehouseId();
        echo Mage::getSingleton('admin/session')->getWarehouseId();
        die;
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('xpos/till');
                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Till was successfully deleted'));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*');
    }

    public function massDeleteAction()
    {
        $tillIds = $this->getRequest()->getParam('till');
        if (!is_array($tillIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select till(s)'));
        } else {
            try {
                foreach ($tillIds as $tillId) {
                    Mage::getModel('xpos/till')->load($tillId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count(row) + 1
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*');
    }

}