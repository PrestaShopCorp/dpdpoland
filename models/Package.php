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

class DpdPolandPackage extends DpdPolandWS
{
	const FILENAME = 'Package';

	public	$id_package;
	public	$id_order;
	public	$sessionId;
	public 	$sessionType;
    public 	$payerNumber;
	public	$id_address_sender;
    public 	$id_address_delivery;
    public	$cod_amount;
    public	$declaredValue_amount;
    public	$ref1;
    public	$ref2;
	public	$additional_info;
	public	$labels_printed = 0;
	public	$date_add;
	public 	$date_upd;

	private $parcels = array();
	private $services = array();
	private $sender = array();
	private $receiver = array();
	private $data = array();

	public function __construct($id_package = null)
	{
		if ($package = $this->getPackage($id_package))
			foreach ($package as $name => $value)
				if (key_exists($name, $this))
					$this->$name = $value;

		if (!$this->date_add)
			$this->date_add = date('Y-m-d H:i:s');

		if (!$this->date_upd)
			$this->date_upd = date('Y-m-d H:i:s');

		parent::__construct();
	}

	public function addParcel($parcel, $additional_info)
	{
		$parcel = array(
			'content' => $parcel['content'],
			'customerData1' => $additional_info,
			'customerData2' => null,
			'customerData3' => null,
			'reference' => Tools::strtoupper(Tools::passwdGen(9, 'NO_NUMERIC')).'_'.(int)$parcel['number'],
			'sizeX' => (float)$parcel['length'],
			'sizeY' => (float)$parcel['width'],
			'sizeZ' => (float)$parcel['height'],
			'weight' => (float)$parcel['weight']
		);

		$this->parcels[] = $parcel;
	}

	private function prepareReceiverAddress()
	{
		$address = new Address((int)$this->id_address_delivery);

		if (Validate::isLoadedObject($address))
		{
			$customer = new Customer((int)$address->id_customer);

			if (Validate::isLoadedObject($customer))
			{
				$this->receiver = array(
					'address' => pSQL($address->address1),
					'city' => pSQL($address->city),
					'company' => pSQL($address->company),
					'countryCode' => pSQL(Country::getIsoById((int)$address->id_country)),
					'email' => pSQL($customer->email),
					'fid' => null,
					'name' => pSQL($address->firstname).' '.pSQL($address->lastname),
					'phone' => $address->phone ? pSQL($address->phone) : pSQL($address->phone_mobile),
					'postalCode' => pSQL(DpdPoland::convertPostcode($address->postcode))
				);
			}
			else
			{
				self::$errors[] = $this->l('Customer does not exists');
				return false;
			}
		}
		else
		{
			self::$errors[] = $this->l('Receiver address does not exists');
			return false;
		}

		return true;
	}

	private function prepareSenderAddress($client_number = 'null')
	{
		$settings = new DpdPolandConfiguration;

		$this->sender = array(
			'address' => pSQL($settings->address),
			'city' => pSQL($settings->city),
			'company' => pSQL($settings->company_name),
			'countryCode' => DpdPoland::POLAND_ISO_CODE,
			'email' => pSQL($settings->email),
			'fid' => pSQL($client_number),
			'name' => pSQL($settings->name_surname),
			'phone' => PSQL($settings->phone),
			'postalCode' => pSQL(DpdPoland::convertPostcode($settings->postcode))
		);
	}

	public function getSessionType()
	{
		return $this->sessionType == 'international' ? 'INTERNATIONAL' : 'DOMESTIC';
	}

	public function getSenderAddress($client_number)
	{
		if (!$this->sender)
			$this->prepareSenderAddress($client_number);

		return $this->sender;
	}

	public function create()
	{
		if ($result = $this->createRemotely())
		{
			if (isset($result['Status']) && $result['Status'] == 'OK')
			{
				$package = $result['Packages']['Package'];
				$this->id_package = (int)$package['PackageId'];
				$this->sessionId = (int)$result['SessionId'];

				if (!$this->saveLocally())
					self::$errors[] = $this->l('Package was successfully created but we were unable to save its data locally');

				return $package;
			}
			else
			{
				if (isset($result['Packages']['InvalidFields']))
					$errors = $result['Packages']['InvalidFields'];
				elseif (isset($result['faultcode']) && isset($result['faultstring']))
					$errors = $result['faultcode'].' : '.$result['faultstring'];
				elseif (isset($result['Packages']['Package']['ValidationDetails']['ValidationInfo']['ErrorId']))
				{
					require_once(_PS_MODULE_DIR_.'dpdpoland/dpdpoland.lang.php');

					$language = new DpdPolandLanguage();
					$error_message = $language->getTranslation($result['Packages']['Package']['ValidationDetails']['ValidationInfo']['ErrorId']);

					if ($error_message && Tools::strtolower($this->context->language->iso_code) != 'pl')
						$errors = $error_message;
					elseif (isset($result['Packages']['Package']['ValidationDetails']['ValidationInfo']['Info']))
						$errors = $result['Packages']['Package']['ValidationDetails']['ValidationInfo']['Info'];
					else
						$errors = $this->module_instance->displayName.' : '.$this->l('Unknown error');
				}
				elseif (isset($result['Packages']['Package']['Parcels']['Parcel']['ValidationDetails']['ValidationInfo']['ErrorId']))
				{
					require_once(_PS_MODULE_DIR_.'dpdpoland/dpdpoland.lang.php');

					$language = new DpdPolandLanguage();
					$error_message = $language->getTranslation($result['Packages']['Package']['Parcels']['Parcel']['ValidationDetails']['ValidationInfo']['ErrorId']);

					if ($error_message && Tools::strtolower($this->context->language->iso_code) != 'pl')
						$errors = $error_message;
					elseif (isset($result['Packages']['Package']['Parcels']['Parcel']['ValidationDetails']['ValidationInfo']['Info']))
						$errors = $result['Packages']['Package']['Parcels']['Parcel']['ValidationDetails']['ValidationInfo']['Info'];
					else
						$errors = $this->module_instance->displayName.' : '.$this->l('Unknown error');
				}
				else
					$errors = $this->module_instance->displayName.' : '.$this->l('Unknown error');

				if (is_array($errors))
				{
					$errors = (array_values($errors) === $errors) ? $errors : array($errors); // array must be multidimentional

					foreach ($errors as $error)
						self::$errors[] = $error['info'];
				}
				else
					self::$errors[] = $errors;

				return false;
			}
		}

		return false;
	}

	public function generateLabels($outputDocFormat = 'PDF', $outputDocPageFormat = 'A4', $policy = 'STOP_ON_FIRST_ERROR')
	{
		if (!in_array($outputDocPageFormat, array(DpdPolandConfiguration::PRINTOUT_FORMAT_A4, DpdPolandConfiguration::PRINTOUT_FORMAT_LABEL)))
			$outputDocPageFormat = DpdPolandConfiguration::PRINTOUT_FORMAT_A4;

		$this->prepareSenderAddress();

		$params = array(
			'dpdServicesParamsV1' => array(
				'policy' => pSQL($policy),
				'session' => array(
					'sessionId' => (int)$this->sessionId,
					'sessionType' => pSQL($this->getSessionType())
				) 
			), 
			'outputDocFormatV1' => pSQL($outputDocFormat),
			'outputDocPageFormatV1' => pSQL($outputDocPageFormat),
			'pickupAddress' => pSQL($this->sender)
		);

		if (!$result = $this->generateSpedLabelsV1($params))
			return false;

		if (isset($result['session']) && $result['session']['statusInfo']['status'] == 'OK')
		{
			$this->labels_printed = 1;
			$this->logLabelsPrintout();
			return base64_decode($result['documentData']);
		}
		else
		{

			$error = isset($result['session']['packages']) ? $result['session']['packages']['statusInfo']['description'] : $result['session']['statusInfo']['description'];
			self::$errors[] = $error;
			return false;
		}
	}

	public static function separatePackagesBySession($ids)
	{
		$international_packages = array();
		$domestic_packages = array();

		foreach ($ids as $id_package)
		{
			$package = new DpdPolandPackage((int)$id_package);
			$session_type = $package->getSessionType();
			if ($session_type == 'INTERNATIONAL')
				$international_packages[] = (int)$id_package;
			elseif ($session_type == 'DOMESTIC')
				$domestic_packages[] = (int)$id_package;
		}

		return array('INTERNATIONAL' => $international_packages, 'DOMESTIC' => $domestic_packages);
	}

	public function generateLabelsForMultiplePackages($package_ids, $outputDocFormat = 'PDF', $outputDocPageFormat = 'LBL_PRINTER', $policy = 'STOP_ON_FIRST_ERROR')
	{
		$sessionType = '';
		$packages = array();

		foreach ($package_ids as $id_package)
		{
			$package = new DpdPolandPackage((int)$id_package);
			if (!$sessionType || $sessionType == $package->getSessionType())
				$sessionType = $package->getSessionType();
			else
			{
				self::$errors[] = $this->l('Manifests of DOMESTIC shipments cannot be mixed with INTERNATIONAL shipments');
				return false;
			}

			$packages[] = array(
				'packageId' => (int)$id_package
			);
		}

		$this->duplicatable_nodes = array('packages');
		$this->prepareSenderAddress();

		$params = array(
			'dpdServicesParamsV1' => array(
				'policy' => pSQL($policy),
				'session' => array(
					'packages' => $packages,
					'sessionType' => pSQL($sessionType)
				) 
			), 
			'outputDocFormatV1' => pSQL($outputDocFormat),
			'outputDocPageFormatV1' => pSQL($outputDocPageFormat),
			'pickupAddress' => $this->sender
		);

		if (!$result = $this->generateSpedLabelsV1($params))
			return false;

		if (isset($result['session']['statusInfo']['status']) && $result['session']['statusInfo']['status'] == 'OK')
		{
			$this->labels_printed = 1;
			foreach ($packages as $id_package)
			{
				$this->id_package = (int)$id_package;
				$this->logLabelsPrintout();
			}
			return base64_decode($result['documentData']);
		}
		else
		{
			$packages = $result['session']['statusInfo'];
			$packages = (array_values($packages) === $packages) ? $packages : array($packages); // array must be multidimentional

			foreach ($packages as $package)
				if (isset($package['description']))
					self::$errors[] = $package['description'];
				elseif (isset($package['status']))
					self::$errors[] = $package['status'];

			return false;
		}
	}

	/**
	 * Creates packages
	 * $payerType (SENDER, THIRD_PARTY)
	 */

	private function createRemotely($payerType = 'SENDER')
	{
		if (!$this->prepareReceiverAddress())
			return false;

		$payer_number = Tools::getValue('dpdpoland_PayerNumber');

		$this->prepareSenderAddress($payer_number);
		$this->prepareServicesData();
		$this->duplicatable_nodes = array('parcels');

		$params = array(
			'openUMLV1' => array(
				'packages' => array(
					'parcels' => $this->parcels,
					'payerType' => pSQL($payerType),
					'receiver' => $this->receiver,
					'ref1' => pSQL($this->ref1),
					'ref2' => pSQL($this->ref2),
					'ref3' => _DPDPOLAND_REFERENCE3_,
					'reference' => null,
					'sender' => $this->sender,
					'services' => $this->services,
					'thirdPartyFID' => null
				)
			),
			'pkgNumsGenerationPolicyV1' => 'STOP_ON_FIRST_ERROR',
			'langCode' => 'PL'
		);

		return $this->generatePackagesNumbersV2($params);
	}

	private function prepareServicesData()
	{
		if ($this->cod_amount !== null)
		{
			$this->services['cod'] = array(
				'amount' => pSQL($this->cod_amount),
				'currency' => pSQL(_DPDPOLAND_CURRENCY_ISO_)
			);
		}

		if ($this->declaredValue_amount !== null)
		{
			$this->services['declaredValue'] = array(
				'amount' => pSQL($this->declaredValue_amount),
				'currency' => pSQL(_DPDPOLAND_CURRENCY_ISO_)
			);
		}
	}

	private function saveLocally()
	{
		return Db::getInstance()->execute('
			INSERT INTO `'._DB_PREFIX_._DPDPOLAND_PACKAGE_DB_.'` (
				`id_package`,
				`id_order`,
				`sessionId`,
				`sessionType`,
				`payerNumber`,
				`id_address_delivery`,
				`id_address_sender`,
				`cod_amount`,
				`declaredValue_amount`,
				`ref1`,
				`ref2`,
				`additional_info`,
				`labels_printed`,
				`date_add`,
				`date_upd`
			)
			VALUES (
				'.(int)$this->id_package.',
				'.(int)$this->id_order.',
				'.(int)$this->sessionId.',
				 "'.pSQL($this->sessionType).'",
				 "'.pSQL($this->payerNumber).'",
				 '.(int)$this->id_address_delivery.',
				 '.(int)$this->id_address_sender.',
				 "'.(float)$this->cod_amount.'",
				 "'.(float)$this->declaredValue_amount.'",
				 "'.pSQL($this->ref1).'",
				 "'.pSQL($this->ref2).'",
				 "'.pSQL($this->additional_info).'",
				 '.(int)$this->labels_printed.',
				 "'.pSQL($this->date_add).'",
				 "'.pSQL($this->date_upd).'"
			)
		');
	}

	private function logLabelsPrintout()
	{
		return Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_._DPDPOLAND_PACKAGE_DB_.'`
			SET `labels_printed`='.(int)$this->labels_printed.'
			WHERE `id_package`='.(int)$this->id_package
		);
	}

	private function getPackage($id_package)
	{
		return Db::getInstance()->getRow('
			SELECT `id_package`, `id_order`, `sessionId`, `sessionType`, `payerNumber`,`id_address_delivery`, `id_address_sender`,
				`cod_amount`, `declaredValue_amount`, `ref1`, `ref2`, `additional_info`, `labels_printed`, `date_add`, `date_upd`
			FROM `'._DB_PREFIX_._DPDPOLAND_PACKAGE_DB_.'`
			WHERE `id_package`='.(int)$id_package
		);
	}

	public static function getInstanceByIdOrder($id_order)
	{
		$id_package = Db::getInstance()->getValue('
			SELECT `id_package`
			FROM `'._DB_PREFIX_._DPDPOLAND_PACKAGE_DB_.'`
			WHERE `id_order`='.(int)$id_order
		);
		return new DpdPolandPackage($id_package);
	}

	public function isManifestPrinted()
	{
		return (int)Db::getInstance()->getValue('
			SELECT COUNT(`id_manifest`)
			FROM `'._DB_PREFIX_._DPDPOLAND_MANIFEST_DB_.'`
			WHERE `id_package`='.(int)$this->id_package
		);
	}

	public function getList($order_by, $order_way, $filter, $start, $pagination)
	{
		$id_shop = (int)Context::getContext()->shop->id;
		$id_lang = (int)Context::getContext()->language->id;

		$list = DB::getInstance()->executeS('
			SELECT
				p.`id_package`								AS `id_package`,
				p.`date_add` 								AS `date_add`,
				p.`id_order` 								AS `id_order`,
				(SELECT COUNT(par.`id_parcel`)
				FROM `'._DB_PREFIX_._DPDPOLAND_PARCEL_DB_.'` par
				WHERE par.`id_package` = p.`id_package`) 	AS `count_parcel`,
				(SELECT parc.`waybill`
				FROM `'._DB_PREFIX_._DPDPOLAND_PARCEL_DB_.'` parc
				WHERE parc.`id_package` = p.`id_package`
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
					SELECT m.`id_manifest`
					FROM `'._DB_PREFIX_._DPDPOLAND_MANIFEST_DB_.'` m
					WHERE m.`id_package` = p.`id_package`
				) '.
			$filter.
			($order_by && $order_way ? ' ORDER BY '.pSQL($order_by).' '.pSQL($order_way) : '').
			($start !== null && $pagination !== null ? ' LIMIT '.(int)$start.', '.(int)$pagination : '')
		);

		if (!$list)
			$list = array();

		return $list;
	}
}