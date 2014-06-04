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
	
class DpdPolandManifest extends DpdPolandWS
{
	public $id_manifest;
	
	public $id_package;
	
	public $date_add;
	
	public $date_upd;
	
	public function __construct($id_manifest = null)
	{
		if ($id_manifest)
			$this->id_manifest = (int)$id_manifest;
		
		if ($id_manifest)
			$this->id_package = (int)$this->getIdPackageByIdManifest((int)$id_manifest);
		
		if (!$this->date_add)
			$this->date_add = date('Y-m-d H:i:s');
			
		if (!$this->date_upd)
			$this->date_upd = date('Y-m-d H:i:s');
			
		parent::__construct();
	}
	
	private function getIdPackageByIdManifest($id_manifest)
	{
		return DB::getInstance()->getValue('
			SELECT `id_package`
			FROM `'._DB_PREFIX_._DPDPOLAND_MANIFEST_DB_.'`
			WHERE `id_manifest` = "'.(int)$id_manifest.'"
		');
	}
	
	private function getIdsPackagesByIdManifest($id_manifest)
	{
		$packages_ids = array();
		
		$packages = DB::getInstance()->executeS('
			SELECT `id_package`
			FROM `'._DB_PREFIX_._DPDPOLAND_MANIFEST_DB_.'`
			WHERE `id_manifest` = "'.(int)$id_manifest.'"
		');
		
		foreach ($packages as $package)
			$packages_ids[] = $package['id_package'];
		
		return $packages_ids;
	}
	
	public function generate($outputDocFormat = 'PDF', $outputDocPageFormat = 'LBL_PRINTER', $policy = 'STOP_ON_FIRST_ERROR')
	{
		$package = new DpdPolandPackage($this->id_package);
		$multiple_packages = DpdPolandManifest::getIdsPackagesByIdManifest((int)$this->id_manifest);
		$package_number = null;
		if (count($multiple_packages) > 1)
		{
			foreach ($multiple_packages as $id_package)
			{
				$current_package = new DpdPolandPackage((int)$id_package);
				if ($package_number === null)
					$package_number = $current_package->payerNumber;
				elseif ($package_number !== $current_package->payerNumber)
					$package_number = 'null';
			}
		}
		else
			$package_number = $package->payerNumber;
		
		$params = array(
			'dpdServicesParamsV1' => array(
				'pickupAddress' => $package->getSenderAddress($package_number),
				'policy' => pSQL($policy),
				'session' => array(
					'sessionId' => (int)$package->sessionId,
					'sessionType' => pSQL($package->getSessionType())
				)
			),
			'outputDocFormatV1' => pSQL($outputDocFormat),
			'outputDocPageFormatV1' => pSQL($outputDocPageFormat)
		);
		
		if ($this->id_manifest)
			$params['dpdServicesParamsV1']['documentId'] = (int)$this->id_manifest;

		$result = $this->generateProtocolV1($params);
		
		if (isset($result['documentData']) || (isset($result['session']) && isset($result['session']['statusInfo']) && $result['session']['statusInfo']['status'] == 'OK'))
		{
			if (!$this->id_manifest)
				$this->id_manifest = (int)$result['documentId'];
				
			$this->saveManifestLocally();
				
			return base64_decode($result['documentData']);
		}
		else
			return false;
	}
	
	public function generateMultiple($package_ids, $outputDocFormat = 'PDF', $outputDocPageFormat = 'LBL_PRINTER', $policy = 'STOP_ON_FIRST_ERROR')
	{
		$sessionType = '';
		$package_number = null;
		foreach ($package_ids as $id_package)
		{
			$package = new DpdPolandPackage((int)$id_package);
			if (!$sessionType || $sessionType == $package->getSessionType())
			{
				$sessionType = $package->getSessionType();
				if ($package_number === null)
					$package_number = $package->payerNumber;
				elseif ($package_number !== $package->payerNumber)
					$package_number = 'null';
			}
			else
			{
				self::$errors[] = $this->l('Manifests of DOMESTIC shipments cannot be mixed with INTERNATIONAL shipments'); // @TODO - not sure about this
				return false;
			}
		}
		
		$this->duplicatable_nodes = array('packages');
		
		$params = array(
			'dpdServicesParamsV1' => array(
				'pickupAddress' => $package->getSenderAddress($package_number),
				'policy' => pSQL($policy),
				'session' => array(
					'sessionType' => pSQL($sessionType),
					'packages' => array()
				)
			),
			'outputDocFormatV1' => pSQL($outputDocFormat),
			'outputDocPageFormatV1' => pSQL($outputDocPageFormat)
		);
		
		foreach ($package_ids as $id_package)
		{
			$params['dpdServicesParamsV1']['session']['packages'][] = array(
				'packageId' => (int)$id_package
			 );
		}

		$result = $this->generateProtocolV1($params);
		
		if (isset($result['session']) && isset($result['session']['statusInfo']) && $result['session']['statusInfo']['status'] == 'OK')
		{
			if (!$this->id_manifest)
				$this->id_manifest = (int)$result['documentId'];
				
			foreach ($package_ids as $id_package)
			{
				$this->id_package = (int)$id_package;
				$this->saveManifestLocally();
			}

			return base64_decode($result['documentData']);
		}
		else
		{
			if (isset($result['session']['statusInfo']['description']))
				self::$errors[] = $result['session']['statusInfo']['description'];
			elseif (isset($result['session']['statusInfo']['status']))
				self::$errors[] = $result['session']['statusInfo']['status'];
			
			return false;
		}
	}
	
	private function saveManifestLocally()
	{
		if (!$this->id_package)
			return false;
		
		return Db::getInstance()->execute('
			INSERT IGNORE INTO `'._DB_PREFIX_._DPDPOLAND_MANIFEST_DB_.'`
				(`id_manifest`, `id_package`, `date_add`, `date_upd`)
			VALUES
				("'.(int)$this->id_manifest.'", "'.(int)$this->id_package.'", "'.pSQL($this->date_add).'", "'.pSQL($this->date_upd).'")
		');
	}
	
	public function getList($order_by, $order_way, $filter, $start, $pagination)
	{
		return Db::getInstance()->executeS('
			SELECT m.`id_manifest` 				AS `id_manifest`,
				COUNT(p.`id_parcel`) 			AS `count_parcels`,
				COUNT(DISTINCT m.`id_package`)	AS `count_orders`,
				m.`date_add` 					AS `date_add`
			FROM `'._DB_PREFIX_._DPDPOLAND_MANIFEST_DB_.'` m
			LEFT JOIN `'._DB_PREFIX_._DPDPOLAND_PARCEL_DB_.'` p ON (p.`id_package` = m.`id_package`)
			GROUP BY `id_manifest`
			'.$filter.'
			ORDER BY '.$order_by.' '.$order_way .
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
	
	public static function getManifestIdByPackageId($id_package)
	{
		return DB::getInstance()->getValue('
			SELECT `id_manifest`
			FROM `'._DB_PREFIX_._DPDPOLAND_MANIFEST_DB_.'`
			WHERE `id_package` = "'.(int)$id_package.'"
		');
	}
}