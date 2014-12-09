<?php
$installer = $this;
$installer->startSetup();

$installer->addAttribute("catalog_category", "xpos_enable",  array(
                                                                  "type"     => "int",
                                                                  "backend"  => "",
                                                                  "frontend" => "",
                                                                  "label"    => "Enable for XPOS",
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
$installer->updateAttribute('catalog_category', 'xpos_enable', 'is_used_for_customer_segment', '1');
$installer->addAttribute("catalog_category", "xpos_name",  array(
                                                                "type"     => "varchar",
                                                                "backend"  => "",
                                                                "frontend" => "",
                                                                "label"    => "XPOS category name",
                                                                "input"    => "text",
                                                                "class"    => "",
                                                                "source"   => "",
                                                                "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                                                                "visible"  => true,
                                                                "required" => false,
                                                                "user_defined"  => false,
                                                                "default" => "",
                                                                "searchable" => false,
                                                                "filterable" => false,
                                                                "comparable" => false,
                                                                'group' => 'XPOS',
                                                                "visible_on_front"  => false,
                                                                "unique"     => false,
                                                                "note"       => ""

                                                           ));
$installer->endSetup();