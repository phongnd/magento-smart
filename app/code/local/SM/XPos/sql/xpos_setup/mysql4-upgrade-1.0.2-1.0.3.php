<?php
$installer = $this;
$installer->startSetup();

$xposHelper = Mage::helper("xpos");

if(!$xposHelper->columnExist($this->getTable('sales/order'), 'till_id')) {
    $installer->run(" ALTER TABLE {$this->getTable('sales/order')} ADD `till_id` int( 2 ) unsigned NULL; ");
}

$installer->endSetup();