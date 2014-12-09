<?php
class SM_XPos_Block_Adminhtml_Config_Frontend_Contact extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected function _getHeaderCommentHtml($element)
    {
        $xposVersion = file_get_contents(Mage::getModuleDir(null, 'SM_XPos') . DS . 'version.txt');
        $content =  <<<TEXT
            <strong>Report Issue : <a href="http://support.smartosc.com/index.php?/Tickets/Submit/RenderForm/2" target="_blank" >Link to support tickets</a></br></strong>
            <strong>Send feedback : <a href="mailto:support@smartosc.com" >support@smartosc.com</a></strong>
            <p><strong>{$xposVersion}</strong></p>
TEXT;
        $element->setComment($content);
        return parent::_getHeaderCommentHtml($element);
    }
}