<?php
/**
 * Created by PhpStorm.
 * User: Smartor
 * Date: 9/30/14
 * Time: 4:17 PM
 */
class SM_XPos_Block_Adminhtml_Index_Orderlist_Renderer_Actionsaved extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    protected function _getValue(Varien_Object $row)
    {
        $html = '';
        $data = $row->getData('increment_id');
        $order = Mage::getModel('sales/order')->load($data, 'increment_id');
        $order_id = $order->getId();
        $user_limit = $this->getRequest()->getParam('user_limited');
        if($user_limit != NULL) Mage::getSingleton('core/session')->setUserLimit($user_limit);
        $limit  = Mage::getSingleton('core/session')->getUserLimit();
        if($limit==0)
            $action = array(
                'reload'=>array('label'=>'Reload','url'=>$this->getUrl('*/*/index', array('order_id' => $order_id, 'action'=>'reload'))),
            );
        else
        $action = array(
            'reload'=>array('label'=>'Reload','url'=>$this->getUrl('*/*/index', array('order_id' => $order_id, 'action'=>'reload'))),
            'invoice'=>array('label'=>'Invoice','url'=>$this->getUrl('*/*/index', array('order_id' => $order_id, 'action'=>'invoice'))),
            'ship'=>array('label'=>'Ship','url'=>$this->getUrl('*/*/index', array('order_id' => $order_id, 'action'=>'ship'))),
            'canceled'=>array('label'=>'Cancel','url'=>$this->getUrl('*/*/index', array('order_id' => $order_id, 'action'=>'cancel')))
        );
        if(!$order->canShip()){
            unset($action['ship']);
        }
        if(!$order->canCancel()){
            unset($action['canceled']);
        }
        if(!$order->canInvoice()){
            unset($action['invoice']);
            //unset($action['reload']);
        }
//        if($order->getStatus()=='completed'){
//            $action['reload'] = 'Reload';
//        }
        if(count($action)==0){
            return '--';
        }else{
            $html .= '<select class="select-order" onchange="onSelectOrder(this.value,'.$order_id.',\'view-order\')"><option value=""> </option>';
            foreach($action as $k=>$v){
                $html .= '<option value="'.$k.'">'.$v['label'].'</option>';
            }
            $html.= '</select>';
        }
        return $html;
    }
}