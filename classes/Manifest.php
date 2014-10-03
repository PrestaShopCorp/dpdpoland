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

class DpdPolandManifest extends DpdPolandObjectModel
{
	public $id_manifest; // incremental ID for ObjectModel

	public $id_manifest_ws; // manifest ID retrieved via webservices

	public $id_package_ws;

	public $date_add;

	public $date_upd;

	private $webservice; // manifest webservices instance

	public static $definition = array(
		'table' => _DPDPOLAND_MANIFEST_DB_,
		'primary' => 'id_manifest',
		'multilang' => false,
		'multishop' => false,
		'fields' => array(
			'id_manifest'			=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'id_manifest_ws'		=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'id_package_ws'			=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'date_add' 				=> 	array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			'date_upd' 				=> 	array('type' => self::TYPE_DATE, 'validate' => 'isDate')
		)
	);

	private function getPackages()
	{
		$packages_ids = array();

		$packages = Db::getInstance()->executeS('
			SELECT `id_package_ws`
			FROM `'._DB_PREFIX_._DPDPOLAND_MANIFEST_DB_.'`
			WHERE `id_manifest_ws` = "'.(int)$this->id_manifest_ws.'"
		');

		foreach ($packages as $package)
			$packages_ids[] = $package['id_package_ws'];

		return $packages_ids;
	}

	public function getList($order_by, $order_way, $filter, $start, $pagination)
	{
		$order_way = Validate::isOrderWay($order_way) ? $order_way : 'ASC';

		return Db::getInstance()->executeS('
			SELECT m.`id_manifest_ws` 				AS `id_manifest_ws`,
				COUNT(p.`id_parcel`) 				AS `count_parcels`,
				COUNT(DISTINCT m.`id_package_ws`)	AS `count_orders`,
				m.`date_add` 						AS `date_add`
			FROM `'._DB_PREFIX_._DPDPOLAND_MANIFEST_DB_.'` m
			LEFT JOIN `'._DB_PREFIX_._DPDPOLAND_PARCEL_DB_.'` p ON (p.`id_package_ws` = m.`id_package_ws`)
			GROUP BY `id_manifest_ws`
			'.$filter.'
			ORDER BY `'.bqSQL($order_by).'` '.pSQL($order_way).
			($start !== null && $pagination !== null ? ' LIMIT '.(int)$start.', '.(int)$pagination : '')
		);
	}

	public static function validateSenderAddresses($package_ids)
	{
		if (!is_array($package_ids))
			return false;

		$first_package = new DpdPolandPackage((int)$package_ids[0]);
		$first_package_address = new Address((int)$first_package->id_address_sender);

		$address_keys = array('country', 'company', 'lastname', 'firstname', 'address1', 'postcode', 'city', 'phone');
		$address = array();

		foreach ($address_keys as $key)
			if (isset($first_package_address->$key))
				$address[$key] = $first_package_address->$key;
			else
				return false;

		foreach ($package_ids as $package_id)
		{
			$package = new DpdPolandPackage((int)$package_id);
			$sender_address = new Address((int)$package->id_address_sender);
			$current_package_sender_address = array();

			foreach ($address_keys as $key)
				if (isset($sender_address->$key))
					$current_package_sender_address[$key] = $sender_address->$key;
				else
					return false;

			$differences = array_diff_assoc($address, $current_package_sender_address);

			if (!empty($differences))
				return false;
		}

		return true;
	}

	public static function getManifestIdByPackageId($id_package_ws)
	{
		return DB::getInstance()->getValue('
			SELECT `id_manifest_ws`
			FROM `'._DB_PREFIX_._DPDPOLAND_MANIFEST_DB_.'`
			WHERE `id_package_ws` = "'.(int)$id_package_ws.'"
		');
	}

	public function generate($output_doc_format = 'PDF', $output_doc_page_format = 'LBL_PRINTER', $policy = 'STOP_ON_FIRST_ERROR')
	{
		if (!$this->webservice)
			$this->webservice = new DpdPolandManifestWS;

		return $this->webservice->generate($this, $output_doc_format, $output_doc_page_format, $policy);
	}

	public function generateMultiple($package_ids, $output_doc_format = 'PDF', $output_doc_page_format = 'LBL_PRINTER', $policy = 'STOP_ON_FIRST_ERROR')
	{
		if (!$this->webservice)
			$this->webservice = new DpdPolandManifestWS;

		return $this->webservice->generateMultiple($package_ids, $output_doc_format, $output_doc_page_format, $policy);
	}
}