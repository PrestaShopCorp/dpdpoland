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

class DpdPolandCSV extends DpdPolandObjectModel
{
	public $id_shop;
	public $date_add;
	public $date_upd;

	public $id_csv;
	public $iso_country;
	public $weight_from;
	public $weight_to;
	public $parcel_price;
	public $id_carrier;
	public $cod_price;

	const COLUMN_COUNTRY 			= 0;
	const COLUMN_WEIGHT_FROM 		= 1;
	const COLUMN_WEIGHT_TO 			= 2;
	const COLUMN_PARCEL_PRICE 		= 3;
	const COLUMN_CARRIER 			= 4;
	const COLUMN_COD_PRICE			= 5;
	const CSV_FILE 					= 'DPD_GEOPOST_CSV_FILE';

	public static $definition = array(
		'table' => _DPDPOLAND_PRICE_RULE_DB_,
		'primary' => 'id_csv',
		'multilang' => false,
		'multishop' => true,
		'fields' => array(
			'id_csv'			=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'id_shop'			=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'iso_country'			=>	array('type' => self::TYPE_STRING, 'validate' => 'isAnything'),
			'weight_from'		=>	array('type' => self::TYPE_STRING, 'validate' => 'isAnything'),
			'weight_to'			=>	array('type' => self::TYPE_STRING, 'validate' => 'isAnything'),
			'parcel_price'		=>	array('type' => self::TYPE_STRING, 'validate' => 'isFloat'),
			'cod_price'			=>	array('type' => self::TYPE_STRING, 'validate' => 'isFloat'),
			'id_carrier'		=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'date_add'			=>	array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			'date_upd'			=>	array('type' => self::TYPE_DATE, 'validate' => 'isDate')
		)
	);

	public static function getAllData($start = '', $limit = '')
	{
		return DB::getInstance()->executeS('
			SELECT `id_csv`, `iso_country`, `weight_from`, `weight_to`, `parcel_price`, `cod_price`, `id_carrier`
			FROM `'._DB_PREFIX_._DPDPOLAND_PRICE_RULE_DB_.'`
			WHERE `id_shop` = "'.(int)Context::getContext()->shop->id.'"
			'.($start && $limit ? 'LIMIT '.(int)$start.', '.(int)$limit : '')
		);
	}

	public static function deleteAllData()
	{
		return DB::getInstance()->Execute('
			DELETE FROM `'._DB_PREFIX_._DPDPOLAND_PRICE_RULE_DB_.'`
			WHERE `id_shop` = "'.(int)Context::getContext()->shop->id.'"
		');
	}

	public static function getCSVData()
	{
		return DB::getInstance()->executeS('
			SELECT `iso_country`, `weight_from`, `weight_to`, `parcel_price`, `id_carrier`, `cod_price`
			FROM `'._DB_PREFIX_._DPDPOLAND_PRICE_RULE_DB_.'`
			WHERE `id_shop` = "'.(int)Context::getContext()->shop->id.'"
		');
	}

	public static function deleteCSVData()
	{
		return DB::getInstance()->Execute('
			FROM `'._DB_PREFIX_._DPDPOLAND_PRICE_RULE_DB_.'`
			WHERE `id_shop` = "'.(int)Context::getContext()->shop->id.'"
		');
	}

	public static function getPrice($total_weight, $id_carrier, $cart)
	{
		$iso_country = '';

		if ($id_country = (int)Tools::getValue('id_country'))
			$iso_country = Country::getIsoById($id_country);
		else
		{
			$address = new Address((int)$cart->id_address_delivery);
			$iso_country = Country::getIsoById((int)$address->id_country);
		}

		$price_rules = DB::getInstance()->executeS('
			SELECT `parcel_price`, `cod_price`, `iso_country`
			FROM `'._DB_PREFIX_._DPDPOLAND_PRICE_RULE_DB_.'`
			WHERE (`iso_country` = "'.pSQL($iso_country).'" OR `iso_country` = "*")
				AND `weight_from` <= "'.pSQL($total_weight).'"
				AND `weight_to` >= "'.pSQL($total_weight).'"
				AND `id_carrier` = "'.(int)$id_carrier.'"
				AND `id_shop` = "'.(int)Context::getContext()->shop->id.'"
		');

		if (!$price_rules)
			return false;

		$available_prices_count = count($price_rules);

		for ($i = 0; $i < $available_prices_count; $i++)
			if ($price_rules[$i]['iso_country'] != '*' && !Country::getByIso($price_rules[$i]['iso_country'])) //if country is not deleted
				unset($price_rules[$i]);

		if (!$price_rules)
			return false;

		$price_rules = $price_rules[0]; //accept first matching rule

		if (!$price_rules['cod_price'])
			$price_rules['cod_price'] = 0; //CSV validation allows empty value of COD price

		$price = $price_rules['parcel_price'];

		if ($id_carrier == _DPDPOLAND_STANDARD_COD_ID_)
			$price += $price_rules['cod_price'];

		return $price;
	}
}