<?php
$installer = $this;
$installer->startSetup();

$xposHelper = Mage::helper("xpos");

if(!$xposHelper->columnExist($this->getTable('xpos/transaction'), 'transac_flag')) {
    $installer->run(" ALTER TABLE {$this->getTable('xpos/transaction')} ADD `transac_flag` int( 2 ) unsigned NOT NULL DEFAULT 1; ");
}

$installer->endSetup();