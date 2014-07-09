<?php
/**
* 2014 DPD Polska Sp. z o.o.
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* prestashop@dpd.com.pl so we can send you a copy immediately.
*
*  @author    JSC INVERTUS www.invertus.lt <help@invertus.lt>
*  @copyright 2014 DPD Polska Sp. z o.o.
*  @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
*  International Registered Trademark & Property of DPD Polska Sp. z o.o.
*/

if (!defined('_PS_VERSION_'))
	exit;

class DpdPolandCountry extends DpdPolandObjectModel
{
	public $id_dpdpoland_country;

	public $id_country;

	public $id_shop;

	public $date_add;

	public $date_upd;

	public $enabled;

	public static $default_enabled_countries = array(
		'AT', 'BE', 'BA', 'BG', 'HR', 'CZ', 'DK', 'EE', 'FI', 'FR', 'GR', 'ES', 'IE', 'LT', 'LU',
		'LV', 'DE', 'NO', 'PT', 'RO', 'RS', 'SK', 'SI', 'SZ', 'SE', 'HU', 'GB', 'IT', 'NL'
	);

	public function __construct($id_dpdpoland_country = null)
	{
		parent::__construct($id_dpdpoland_country);
		$this->id_shop = (int)Context::getContext()->shop->id;
	}

	public static $definition = array(
		'table' => _DPDPOLAND_COUNTRY_DB_,
		'primary' => 'id_dpdpoland_country',
		'multilang_shop' => true,
		'multishop' => true,
		'fields' => array(
			'id_country'			=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'id_shop'				=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'enabled'				=>	array('type' => self::TYPE_INT, 'validate' => 'isInt'),
			'date_add' 				=> 	array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			'date_upd' 				=> 	array('type' => self::TYPE_DATE, 'validate' => 'isDate')
		)
	);

	public function getList($order_by, $order_way, $filter, $start, $pagination)
	{
		$id_shop = (int)Context::getContext()->shop->id;
		$id_lang = (int)Context::getContext()->language->id;

		if (version_compare(_PS_VERSION_, '1.5', '<'))
			$countries = DB::getInstance()->executeS('
				SELECT
					c.`id_country`									AS `id_country`,
					cl.`name` 										AS `name`,
					c.`iso_code` 									AS `iso_code`,
					IF(dpdc.`enabled` IS NULL, 1, dpdc.`enabled`) 	AS `enabled`
				FROM `'._DB_PREFIX_.'country` c
				LEFT JOIN `'._DB_PREFIX_._DPDPOLAND_COUNTRY_DB_.'` dpdc ON (dpdc.`id_country` = c.`id_country` AND dpdc.`id_shop` = "'.(int)$id_shop.'")
				LEFT JOIN `'._DB_PREFIX_.'country_lang` cl ON (cl.`id_country` = c.`id_country` AND cl.`id_lang` = "'.(int)$id_lang.'")
				 '.
				$filter.
				($order_by && $order_way ? ' ORDER BY '.bqSQL($order_by).' '.pSQL($order_way) : '').
				($start !== null && $pagination !== null ? ' LIMIT '.(int)$start.', '.(int)$pagination : '')
			);
		else
			$countries = DB::getInstance()->executeS('
				SELECT
					c.`id_country`									AS `id_country`,
					cl.`name` 										AS `name`,
					c.`iso_code` 									AS `iso_code`,
					IF(dpdc.`enabled` IS NULL, 1, dpdc.`enabled`) 	AS `enabled`
				FROM `'._DB_PREFIX_.'country` c
				LEFT JOIN `'._DB_PREFIX_._DPDPOLAND_COUNTRY_DB_.'` dpdc ON (dpdc.`id_country` = c.`id_country` AND dpdc.`id_shop` = "'.(int)$id_shop.'")
				LEFT JOIN `'._DB_PREFIX_.'country_shop` cs ON (cs.`id_country` = c.`id_country`)
				LEFT JOIN `'._DB_PREFIX_.'country_lang` cl ON (cl.`id_country` = c.`id_country` AND cl.`id_lang` = "'.(int)$id_lang.'")
				WHERE cs.`id_shop` = "'.(int)$id_shop.'" '.
				$filter.
				($order_by && $order_way ? ' ORDER BY '.bqSQL($order_by).' '.pSQL($order_way) : '').
				($start !== null && $pagination !== null ? ' LIMIT '.(int)$start.', '.(int)$pagination : '')
			);

		if (!$countries)
			$countries = array();

		return $countries;
	}

	public static function getIdByCountry($id_country)
	{
		$id_shop = (int)Context::getContext()->shop->id;

		return DB::getInstance()->getValue('
			SELECT `id_dpdpoland_country`
			FROM `'._DB_PREFIX_._DPDPOLAND_COUNTRY_DB_.'`
			WHERE `id_shop` = "'.(int)$id_shop.'"
				AND `id_country` = "'.(int)$id_country.'"
		');
	}

	public static function getDisabledCountriesIDs()
	{
		$id_shop = (int)Context::getContext()->shop->id;

		$countries = DB::getInstance()->executeS('
			SELECT `id_country`
			FROM `'._DB_PREFIX_._DPDPOLAND_COUNTRY_DB_.'`
			WHERE `id_shop` = "'.(int)$id_shop.'"
				AND `enabled` = "0"
		');

		if (!$countries)
			$countries = array();

		$countries_array = array();

		foreach ($countries as $country)
			$countries_array[] = $country['id_country'];

		return $countries_array;
	}

	public static function addCountry($id_country, $id_shop, $enabled = 0)
	{
		return DB::getInstance()->Execute('
			INSERT INTO `'._DB_PREFIX_._DPDPOLAND_COUNTRY_DB_.'`
				(`id_shop`, `id_country`, `enabled`, `date_add`, `date_upd`)
			VALUES
				("'.(int)$id_shop.'", "'.(int)$id_country.'", "'.(int)$enabled.'", "'.date('Y-m-d H:i:s').'", "'.date('Y-m-d H:i:s').'")
		');
	}
}