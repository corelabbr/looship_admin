<?php

/**
 * This source file is subject to the MIT License.
 * It is also available through http://opensource.org/licenses/MIT
 *
 * @category  Looship
 * @package   Looship_Admin
 * @author    Looship <contato@looship.com.br>
 * @copyright 2023 Looship (http://looship.com.br)
 * @license   http://opensource.org/licenses/MIT MIT
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

// Add volume to prduct attribute set
$attributeCode = 'taxonomy';
$config = array(
    'position' => 1,
    'required' => 0,
    'label'    => 'Taxonomia',
    'type'     => 'int',
    'input'    => 'select',
    'apply_to' => 'simple,bundle,grouped,configurable',
    'note'     => 'Último nível da Taxonomia do Google',
    'option' => array(
        'values' => array(
            'Adultos',
            'Armas',
            'Roupas',
        )
    ),
);

$setup->addAttribute('catalog_product', $attributeCode, $config);
$installer->endSetup();