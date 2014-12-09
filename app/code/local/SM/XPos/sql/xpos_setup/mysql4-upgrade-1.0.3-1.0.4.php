<?php
    $installer = $this;
    $installer->startSetup();


    $installer->addAttribute("catalog_category", "xpos_default",  array(
        "type"     => "int",
        "backend"  => "xpos/backend_defaultCategory",
        "frontend" => "",
        "label"    => "Show default on XPOS",
        "input"    => "select",
        "class"    => "",
        "source"   => "eav/entity_attribute_source_boolean",
        "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        "visible"  => true,
        "required" => false,
        "user_defined"  => false,
        "default" => true,
        "searchable" => false,
        "filterable" => false,
        "comparable" => false,
        'group' => 'XPOS',
        "visible_on_front"  => false,
        "unique"     => false,
        "note"       => ""

    ));
    $installer->updateAttribute('catalog_category', 'xpos_default', 'is_used_for_customer_segment', '1');

    $installer->endSetup();