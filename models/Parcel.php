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
	
class DpdPolandParcel extends DpdPolandObjectModel
{
	public $id_parcel;
	
	public $id_package;
	
	public $waybill;
	
	public $content;
	
	public $weight;
	
	public $height;
	
	public $length;
	
	public $width;
	
	public $number; /* parcel number */
	
	public $date_add;
	
	public $date_upd;
	
	
	public static $definition = array(
		'table' => _DPDPOLAND_PARCEL_DB_,
		'primary' => 'id_parcel',
		'multilang' => false,
		'multishop' => false,
		'fields' => array(
			'id_parcel'		=>	array('type' => self::TYPE_INT, 	'validate' => 'isUnsignedInt'),
			'id_package'	=>	array('type' => self::TYPE_INT, 	'validate' => 'isUnsignedInt'),
			'waybill'		=>	array('type' => self::TYPE_STRING, 	'validate' => 'isAnything'),
			'content'		=>	array('type' => self::TYPE_STRING, 	'validate' => 'isAnything'),
			'weight'		=>	array('type' => self::TYPE_FLOAT, 	'validate' => 'isUnsignedFloat'),
			'height'		=>	array('type' => self::TYPE_FLOAT, 	'validate' => 'isUnsignedFloat'),
			'length'		=>	array('type' => self::TYPE_FLOAT, 	'validate' => 'isUnsignedFloat'),
			'width'			=>	array('type' => self::TYPE_FLOAT, 	'validate' => 'isUnsignedFloat'),
			'number'		=>	array('type' => self::TYPE_INT, 	'validate' => 'isUnsignedInt'),
			'date_add'		=>	array('type' => self::TYPE_DATE, 	'validate' => 'isDate'),
			'date_upd'		=>	array('type' => self::TYPE_DATE, 	'validate' => 'isDate')
		)
	);
	
	public static function getParcels($id_order, $id_package = null)
	{
		if ($id_package)
		{
			$parcels = Db::getInstance()->executeS('
				SELECT `id_parcel`, `content`, `weight`, `height`, `length`, `width`, `number`
				FROM `'.pSQL(_DB_PREFIX_.self::$definition['table']).'`
				WHERE `id_package`='.(int)$id_package
			);
			return $parcels;
		}
		
		$products = DpdPolandParcelProduct::getShippedProducts($id_order);
		
		$parcels = array();
		$content = '';
		$weight = $height = $length = $width = 0;
		
		$products_count = count($products);
		
		if ($products_count == 1)
		{
			$product = reset($products);
			$height = DpdPoland::convertDimention($product['height']);
			$length = DpdPoland::convertDimention($product['length']);
			$width = DpdPoland::convertDimention($product['width']);
		}

		foreach ($products as $product)
		{
			$content .= $product['id_product'].'_'.$product['id_product_attribute'];
			if (--$products_count)
				$content .=', ';
			$weight += DpdPoland::convertWeight($product['weight']);
		}
		
		$parcels[] = array(
			'number' => 1,
			'content' => $content,
			'weight' => $weight,
			'height' => sprintf('%.6f',$height),
			'length' => sprintf('%.6f',$length),
			'width' => sprintf('%.6f',$width)
		);
		
		return $parcels;
	}
	
	public function getList($order_by, $order_way, $filter, $start, $pagination)
	{
		$id_shop = (int)Context::getContext()->shop->id;
		$id_lang = (int)Context::getContext()->language->id;
		
		$list = DB::getInstance()->executeS('
			SELECT
				p.`id_order` 								AS `id_order`,
				par.`waybill`								AS `id_parcel`,
				CONCAT(a.`firstname`, " ", a.`lastname`) 	AS `receiver`,
				cl.`name` 									AS `country`,
				a.`postcode` 								AS `postcode`,
				a.`city`									AS `city`,
				a.`address1`								AS `address`,
				p.`date_add` 								AS `date_add`
			FROM `'._DB_PREFIX_._DPDPOLAND_PARCEL_DB_.'` par
			LEFT JOIN `'._DB_PREFIX_._DPDPOLAND_PACKAGE_DB_.'` p ON (p.`id_package` = par.`id_package`)
			LEFT JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_order` = p.`id_order`)
			LEFT JOIN `'._DB_PREFIX_.'address` a ON (a.`id_address` = p.`id_address_delivery`)
			LEFT JOIN `'._DB_PREFIX_.'country_lang` cl ON (cl.`id_country` = a.`id_country` AND cl.`id_lang` = "'.(int)$id_lang.'")'.
			(version_compare(_PS_VERSION_, '1.5', '<') ? ' ' : 'WHERE o.`id_shop` = "'.(int)$id_shop.'" ').
			$filter.
			($order_by && $order_way ? ' ORDER BY '.pSQL($order_by).' '.pSQL($order_way) : '').
			($start !== null && $pagination !== null ? ' LIMIT '.(int)$start.', '.(int)$pagination : '')
		);
		
		if (!$list)
			$list = array();
		
		return $list;
	}
}