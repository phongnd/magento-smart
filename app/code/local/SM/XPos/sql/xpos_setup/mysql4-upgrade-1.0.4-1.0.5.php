<?php
    $installer = $this;
    $installer->startSetup();

    $xposHelper = Mage::helper("xpos");

    if(!$xposHelper->columnExist($this->getTable('sales/order'), 'xpos_app_order_id')){
        $installer->run("ALTER TABLE {$this->getTable('sales/order')} ADD `xpos_app_order_id` VARCHAR( 255 ) NULL;");
    }

    $installer->endSetup();