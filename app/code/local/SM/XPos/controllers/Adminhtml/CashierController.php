<?php

require_once(BP . DS . 'app' . DS . 'code' . DS . 'core' . DS . 'Mage' . DS . 'Adminhtml' . DS . 'controllers' . DS . 'Report' . DS . 'SalesController.php');

class SM_XPos_Adminhtml_CashierController extends Mage_Adminhtml_Controller_Action {
    const XPOST_CUSTOMER_ROLE_USER = 0;
    const XPOST_CUSTOMER_ROLE_ADMIN = 1;

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/sales/xpos/cashier');
    }

    public function indexAction(){
        $this->_title("Manager Cashiers - XPos");
        $this->loadLayout();
        $this->renderLayout();
    }

    public function newAction(){
        $this->_forward('edit');
    }

    public function editAction(){
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('xpos/user')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);

            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('cashier_data', $model);

            $this->loadLayout();

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Cashier Manager'), Mage::helper('adminhtml')->__('Cashier Manager'));

            $this->_addContent($this->getLayout()->createBlock('xpos/adminhtml_cashier_edit'))
                ->_addLeft($this->getLayout()->createBlock('xpos/adminhtml_cashier_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('xpos')->__('Cashier does not exist'));
            $this->_redirect('*/*/');
        }

    }

    public function saveAction(){
        if ($data = $this->getRequest()->getPost()) {

            /**
             * Unset address information if user input address information then choose address type virtual
             */

            $cashier_id = $this->getRequest()->getParam('id');
            $full_user = 1;
            $limited_user = 0;
            $username = $data['username'];
            if (isset($username)) {
                $cashiers = Mage::getModel('xpos/user')->getCollection();
                foreach($cashiers as $row) {
                    if ($username == $row->getUsername() && $row->getXposUserId() != $cashier_id) {
                        Mage::getSingleton('adminhtml/session')->addError($this->__('Duplicate cashier username, please use another username for cashier'));
                        Mage::getSingleton('adminhtml/session')->setFormData($data);
                        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                        return;
                    }
                }
            }

            if($this->getRequest()->getParam('id') == ''){
                $data['created_time'] = time();
            }

            $data['modified_time'] = time();
            $data['is_active'] = isset($data['is_active']) ? 1 : 0;
            $data['type'] = isset($data['type']) ? $full_user : $limited_user;

            $model = Mage::getModel('xpos/user');
            $model->setData($data)->setId($this->getRequest()->getParam('id'));

            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('xpos')->__('Cashier was successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('xpos')->__('Unable to find cashier to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('xpos/user');

                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Cashier was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
    }

    public function massDeleteAction() {
        $cashierIds = $this->getRequest()->getParam('cashier');
        if (!is_array($cashierIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select cashier(s)'));
        } else {
            try {
                foreach ($cashierIds as $cashierId) {
                    $cashier = Mage::getModel('xpos/user')->load($cashierId);
                    $cashier->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count(row)+1
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }

    public function loginAction(){
        $xpos_user_username = $this->getRequest()->getParam('xpos_user_username');
        $xpos_user_password = $this->getRequest()->getParam('xpos_user_password');

        $data_cashier = Mage::getModel('xpos/user')->getCollection();

        $user_admin = Mage::getSingleton('admin/session');
        $user_admin_id = $user_admin->getUser()->getUserId();

        $data_cashier->addFieldToFilter('username',array('eq' => $xpos_user_username));
        $data_cashier->addFieldToFilter('password',array('eq' => $xpos_user_password));
        $data_cashier->addFieldToFilter('user_id',array('eq' => $user_admin_id));
        $data_cashier->addFieldToFilter('is_active',array('eq' => 1));

        foreach($data_cashier as $row){
            $cashier['xpos_user_id'] = $row->getXposUserId();
            $cashier['username'] = $row->getUsername();
            $cashier['email'] = $row->getEmail();
            $cashier['firstname'] = $row->getFirstname();
            $cashier['lastname'] = $row->getLastname();
            $cashier['type'] = $row->getType();
            Mage::register('is_user_limited',$row->getType());
            Mage::getSingleton('adminhtml/session')->setData('is_user_limited',$row->getType());
        }
        return $this->getResponse()->setBody(json_encode($cashier));
    }

}