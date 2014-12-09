<?php
$installer = $this;
$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('xpos/report')};
CREATE TABLE {$this->getTable('xpos/report')} (
  `report_id` int(11) unsigned NOT NULL auto_increment,
  `created_time` datetime NULL,
  `cashier_id` int(11) unsigned NOT NULL DEFAULT 0,
  `store_id` int(11) unsigned NOT NULL DEFAULT 0,
  `till_id` int(11) unsigned NOT NULL DEFAULT 0,
  `warehouse_id` int(11) unsigned NOT NULL DEFAULT 0,
  `order_total` int(11) unsigned NOT NULL DEFAULT 0,
  `amount_total` float NULL DEFAULT 0,
  `transfer_amount` float NULL DEFAULT 0,
  `tax_amount` float NULL DEFAULT 0,
  `refund_amount` float NULL DEFAULT 0,
  `discount_amount` float NULL DEFAULT 0,
  `cash_system` float NULL DEFAULT 0,
  `cash_count` float NULL DEFAULT 0,
  `check_system` float NULL DEFAULT 0,
  `check_count` float NULL DEFAULT 0,
  `cc_system` float NULL DEFAULT 0,
  `cc_count` float NULL DEFAULT 0,
  `other_system` float NULL DEFAULT 0,
  `other_count` float NULL DEFAULT 0,
  PRIMARY KEY (`report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('xpos/denomination')};
CREATE TABLE {$this->getTable('xpos/denomination')} (
  `deno_id` int(11) unsigned NOT NULL auto_increment,
  `deno_name` varchar(255) NOT NULL default '',
  `deno_value` float NULL DEFAULT 0,
  `currency_id` int(11) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`deno_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$currency = array();
//USD
$currency[1]['1 cent']=0.01;
$currency[1]['5 cent']=0.05;
$currency[1]['10 cent']=0.1;
$currency[1]['25 cent']=0.25;
$currency[1]['50 cent']=0.5;
$currency[1]['$1 note']=1;
$currency[1]['$5 note']=5;
$currency[1]['$10 note']=10;
$currency[1]['$20 note']=20;
$currency[1]['$50 note']=50;
$currency[1]['$100 note']=100;
//AUD
$currency[2]['5 cent']=0.05;
$currency[2]['10 cent']=0.1;
$currency[2]['20 cent']=0.2;
$currency[2]['50 cent']=0.5;
$currency[2]['$1 coin']=1;
$currency[2]['$2 coin']=2;
$currency[2]['$5 note']=5;
$currency[2]['$10 note']=10;
$currency[2]['$20 note']=20;
$currency[2]['$50 note']=50;
$currency[2]['$100 note']=100;
//EUD
$currency[3]['1 cent']=0.01;
$currency[3]['2 cent']=0.02;
$currency[3]['5 cent']=0.05;
$currency[3]['10 cent']=0.1;
$currency[3]['20 cent']=0.2;
$currency[3]['50 cent']=0.5;
$currency[3]['€1 coin']=1;
$currency[3]['€2 coin']=2;
$currency[3]['€5 note']=5;
$currency[3]['€10 note']=10;
$currency[3]['€20 note']=20;
$currency[3]['€50 note']=50;
$currency[3]['€100 note']=100;
$currency[3]['€200 note']=200;
$currency[3]['€500 note']=500;
//GBP
$currency[4]['1 penny']=0.01;
$currency[4]['2 pence']=0.02;
$currency[4]['5 pence']=0.05;
$currency[4]['10 pence']=0.1;
$currency[4]['20 pence']=0.2;
$currency[4]['25 pence']=0.25;
$currency[4]['50 pence']=0.5;
$currency[4]['£1 coin']=1;
$currency[4]['£2 coin']=1;
$currency[4]['£5 coin']=1;
$currency[4]['£5 note']=5;
$currency[4]['£10 note']=10;
$currency[4]['£20 note']=20;
$currency[4]['£50 note']=50;
$currency[4]['£100 note']=100;

$query = array();
foreach($currency as $id=>$val){
    foreach($val as $name => $value){
        $query[] = '(\'' . $name .'\','. $value .','. $id . ')';
    }
}
$installer->run('INSERT INTO `'.$this->getTable('xpos/denomination').'` (`deno_name`,`deno_value`,`currency_id`) VALUES '
    . implode(',', $query));

$installer->endSetup();