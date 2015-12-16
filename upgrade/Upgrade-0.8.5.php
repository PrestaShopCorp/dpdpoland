<?php

if (!defined('_PS_VERSION_'))
    exit;

/**
 * Process Module upgrade to 0.8.5 version
 * @param $module
 * @return bool upgrade result
 */
function upgrade_module_0_8_5($module)
{
    return Db::getInstance()->execute('
        ALTER TABLE `'._DB_PREFIX_._DPDPOLAND_PRICE_RULE_DB_.'`
        MODIFY COLUMN `weight_from` float,
        MODIFY COLUMN `weight_to` float'
    );
}