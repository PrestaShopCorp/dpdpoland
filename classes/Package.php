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

class DpdPolandPackage extends DpdPolandObjectModel
{
	public $id_package;

	public $id_package_ws;

	public $id_order;

	public $sessionId;

	public $sessionType;

	public $payerNumber;

	public $id_address_sender;

	public $id_address_delivery;

	public $cod_amount;

	public $declaredValue_amount;

	public $ref1;

	public $ref2;

	public $additional_info;

	public $labels_printed = 0;

	public $date_add;

	public $date_upd;

	private $webservice; /* package webservices instance */

	public static $definition = array(
		'table' => _DPDPOLAND_PACKAGE_DB_,
		'primary' => 'id_package',
		'multilang' => false,
		'multishop' => false,
		'fields' => array(
			'id_package'			=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'id_package_ws'			=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'id_order'				=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'sessionId'				=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'sessionType'			=>	array('type' => self::TYPE_STRING, 'validate' => 'isString'),
			'payerNumber'			=>	array('type' => self::TYPE_STRING, 'validate' => 'isString'),
			'id_address_sender'		=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'id_address_delivery'	=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'cod_amount'			=>	array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
			'declaredValue_amount'	=>	array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
			'ref1'					=>	array('type' => self::TYPE_STRING, 'validate' => 'isString'),
			'ref2'					=>	array('type' => self::TYPE_STRING, 'validate' => 'isString'),
			'additional_info'		=>	array('type' => self::TYPE_STRING, 'validate' => 'isString'),
			'labels_printed'		=>	array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'date_add' 				=> 	array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			'date_upd' 				=> 	array('type' => self::TYPE_DATE, 'validate' => 'isDate')
		)
	);

	/* Object identified by id_package_ws rather than id_package
	 * $id_package is used only as a primary field by ObjectModel
	 */
	public function __construct($id_package_ws = null)
	{
		$id_package = $this->getPackageIdByPackageIdWs($id_package_ws);

		parent::__construct($id_package);
	}

	private function getPackageIdByPackageIdWs($id_package_ws)
	{
		return Db::getInstance()->getValue('SELECT `id_package`
											FROM `'._DB_PREFIX_._DPDPOLAND_PACKAGE_DB_.'`
											WHERE `id_package_ws`='.(int)$id_package_ws);
	}

	public static function getInstanceByIdOrder($id_order)
	{
		$id_package_ws = Db::getInstance()->getValue('
			SELECT `id_package_ws`
			FROM `'._DB_PREFIX_._DPDPOLAND_PACKAGE_DB_.'`
			WHERE `id_order`='.(int)$id_order
		);

		return new DpdPolandPackage($id_package_ws);
	}

	public function isManifestPrinted()
	{
		return (int)Db::getInstance()->getValue('
			SELECT COUNT(`id_manifest_ws`)
			FROM `'._DB_PREFIX_._DPDPOLAND_MANIFEST_DB_.'`
			WHERE `id_package_ws`='.(int)$this->id_package_ws
		);
	}

	public function getSessionType()
	{
		return $this->sessionType == 'international' ? 'INTERNATIONAL' : 'DOMESTIC';
	}

	public function getList($order_by, $order_way, $filter, $start, $pagination)
	{
		$order_way = Validate::isOrderWay($order_way) ? $order_way : 'ASC';

		$id_shop = (int)Context::getContext()->shop->id;
		$id_lang = (int)Context::getContext()->language->id;

		$list = DB::getInstance()->executeS('
			SELECT
				p.`id_package_ws`							AS `id_package_ws`,
				p.`date_add` 								AS `date_add`,
				p.`id_order` 								AS `id_order`,
				(SELECT COUNT(par.`id_parcel`)
				FROM `'._DB_PREFIX_._DPDPOLAND_PARCEL_DB_.'` par
				WHERE par.`id_package_ws` = p.`id_package_ws`) 	AS `count_parcel`,
				(SELECT parc.`waybill`
				FROM `'._DB_PREFIX_._DPDPOLAND_PARCEL_DB_.'` parc
				WHERE parc.`id_package_ws` = p.`id_package_ws`
				ORDER BY parc.`id_parcel`
				LIMIT 1) 									AS `package_number`,
				CONCAT(a.`firstname`, " ", a.`lastname`) 	AS `receiver`,
				cl.`name` 									AS `country`,
				a.`postcode` 								AS `postcode`,
				a.`city`									AS `city`,
				a.`address1`								AS `address`
			FROM `'._DB_PREFIX_._DPDPOLAND_PACKAGE_DB_.'` p
			LEFT JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_order` = p.`id_order`)
			LEFT JOIN `'._DB_PREFIX_.'address` a ON (a.`id_address` = p.`id_address_delivery`)
			LEFT JOIN `'._DB_PREFIX_.'country_lang` cl ON (cl.`id_country` = a.`id_country` AND cl.`id_lang` = "'.(int)$id_lang.'")
			WHERE '.(version_compare(_PS_VERSION_, '1.5', '<') ? '' : 'o.`id_shop` = "'.(int)$id_shop.'" AND ').'
				NOT EXISTS(
					SELECT m.`id_manifest_ws`
					FROM `'._DB_PREFIX_._DPDPOLAND_MANIFEST_DB_.'` m
					WHERE m.`id_package_ws` = p.`id_package_ws`
				) '.
			$filter.
			($order_by && $order_way ? ' ORDER BY `'.bqSQL($order_by).'` '.pSQL($order_way) : '').
			($start !== null && $pagination !== null ? ' LIMIT '.(int)$start.', '.(int)$pagination : '')
		);

		if (!$list)
			$list = array();

		return $list;
	}

	public static function separatePackagesBySession($ids)
	{
		$international_packages = array();
		$domestic_packages = array();

		foreach ($ids as $id_package_ws)
		{
			$package = new DpdPolandPackage((int)$id_package_ws);
			$session_type = $package->getSessionType();
			if ($session_type == 'INTERNATIONAL')
				$international_packages[] = (int)$id_package_ws;
			elseif ($session_type == 'DOMESTIC')
				$domestic_packages[] = (int)$id_package_ws;
		}

		return array('INTERNATIONAL' => $international_packages, 'DOMESTIC' => $domestic_packages);
	}

	public function addParcel($parcel, $additional_info)
	{
		if (!$this->webservice)
			$this->webservice = new DpdPolandPackageWS;

		$this->webservice->addParcel($parcel, $additional_info);
	}

	public function create()
	{
		if (!$this->webservice)
			$this->webservice = new DpdPolandPackageWS;

		return $this->webservice->create($this);
	}

	public function generateLabels($outputDocFormat = 'PDF', $outputDocPageFormat = 'A4', $policy = 'STOP_ON_FIRST_ERROR')
	{
		if (!$this->webservice)
			$this->webservice = new DpdPolandPackageWS;

		return $this->webservice->generateLabels($this, $outputDocFormat, $outputDocPageFormat, $policy);
	}

	public function generateLabelsForMultiplePackages($package_ids, $outputDocFormat = 'PDF', $outputDocPageFormat = 'LBL_PRINTER', $policy = 'STOP_ON_FIRST_ERROR')
	{
		if (!$this->webservice)
			$this->webservice = new DpdPolandPackageWS;

		return $this->webservice->generateLabelsForMultiplePackages($package_ids, $outputDocFormat, $outputDocPageFormat, $policy);
	}

	public function getSenderAddress($package_number)
	{
		if (!$this->webservice)
			$this->webservice = new DpdPolandPackageWS;

		return $this->webservice->getSenderAddress($package_number);
	}
}