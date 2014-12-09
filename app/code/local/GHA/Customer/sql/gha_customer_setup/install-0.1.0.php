<?php
/**
 * Created by PhpStorm.
 * User: smartosc
 * Date: 11/28/14
 * Time: 9:12 AM
 * @var $installer Mage_Eav_Model_Entity_Setup
 */

$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$installer
	->addAttribute('customer','customer_type', array(
		'type' => 'varchar',
		'label' => 'Customer Type',
		'visible' => true,
		'required' => 'true',
		'input' => 'text',
		'user_defined' => 'false',
		'default' => 'vendo'
	))
	->addAttribute('customer','manager_vendor',array(
		'type' => 'varchar',
		'label' => 'manager_vendor',
		'visible' => true,
		'required' => false,
		'input' => 'text',
		'user_defined' => false,
		'default' => '',
	));

$installer->endSetup();
