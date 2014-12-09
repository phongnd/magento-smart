<?php
require_once('Mage/Adminhtml/controllers/Sales/Order/CreditmemoController.php');
class SM_XPos_Adminhtml_AjaxcreditmemoController extends Mage_Adminhtml_Sales_Order_CreditmemoController
{
    public function saveAction()
    {
        $result = array();
        $data = $this->getRequest()->getPost('creditmemo');
        Mage::getSingleton('adminhtml/session')->setData("credit_data",$data);
        if (!empty($data['comment_text'])) {
            Mage::getSingleton('adminhtml/session')->setCommentText($data['comment_text']);
        }

        try {
            $creditmemo = $this->_initCreditmemo();
            if ($creditmemo) {
                if (($creditmemo->getGrandTotal() <=0) && (!$creditmemo->getAllowZeroGrandTotal())) {
                    $result['status'] = '0';
                    $result['messages'] = $this->__('Credit memo\'s total must be positive.');
                }

                $comment = '';
                if (!empty($data['comment_text'])) {
                    $creditmemo->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );
                    if (isset($data['comment_customer_notify'])) {
                        $comment = $data['comment_text'];
                    }
                }

                if (isset($data['do_refund'])) {
                    $creditmemo->setRefundRequested(true);
                }
                if (isset($data['do_offline'])) {
                    $creditmemo->setOfflineRequested((bool)(int)$data['do_offline']);
                }

                $creditmemo->register();
                if (!empty($data['send_email'])) {
                    $creditmemo->setEmailSent(true);
                }

                $creditmemo->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                $this->_saveCreditmemo($creditmemo);
                $creditmemoId = $creditmemo->getId();
                $creditmemo->sendEmail(!empty($data['send_email']), $comment);
                $result['status'] = '1';
                $result['messages'] = $this->__('The credit memo has been created.');
                $result['creditmemo_id'] = $creditmemoId;
                Mage::getSingleton('adminhtml/session')->getCommentText(true);

            } else {
                $this->_forward('noRoute');
                return;
            }
        } catch (Mage_Core_Exception $e) {
            $result['status'] = '0';
            $result['messages'] = $e->getMessage();
            Mage::getSingleton('adminhtml/session')->setFormData($data);
        } catch (Exception $e) {
            Mage::logException($e);
            $result['status'] = '0';
            $result['messages'] = $this->__('Cannot save the credit memo.');
        }
        echo json_encode($result);
    }

}