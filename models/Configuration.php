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

class DpdPolandConfiguration
{
	const LOGIN 						= 'DPDPOLAND_LOGIN';
	const PASSWORD 						= 'DPDPOLAND_PASSWORD';
	const CLIENT_NUMBER 				= 'DPDPOLAND_CLIENT_NUMBER';
	const CLIENT_NAME 					= 'DPDPOLAND_CLIENT_NAME';
	const COMPANY_NAME 					= 'DPDPOLAND_COMPANY_NAME';
	const NAME_SURNAME 					= 'DPDPOLAND_NAME_SURNAME';
	const ADDRESS 						= 'DPDPOLAND_ADDRESS';
	const POSTCODE 						= 'DPDPOLAND_POSTCODE';
	const CITY 							= 'DPDPOLAND_CITY';
	const EMAIL 						= 'DPDPOLAND_EMAIL';
	const PHONE 						= 'DPDPOLAND_PHONE';

	const CARRIER_STANDARD 				= 'DPDPOLAND_CARRIER_STANDARD';
	const CARRIER_STANDARD_COD 			= 'DPDPOLAND_CARRIER_STANDARD_COD';
	const CARRIER_CLASSIC 				= 'DPDPOLAND_CARRIER_CLASSIC';

	const PRICE_CALCULATION_TYPE		= 'DPDPOLAND_PRICE_CALCULATION';

	const WEIGHT_CONVERSATION_RATE 		= 'DPDPOLAND_WEIGHT_RATE';
	const DIMENSION_CONVERSATION_RATE 	= 'DPDPOLAND_DIMENSION_RATE';
	const WS_URL 						= 'DPDPOLAND_WS_URL';

	const CARRIER_STANDARD_ID			= 'DPDPOLAND_STANDARD_ID';
	const CARRIER_STANDARD_COD_ID		= 'DPDPOLAND_STANDARD_COD_ID';
	const CARRIER_CLASSIC_ID			= 'DPDPOLAND_CLASSIC_ID';

	const CUSTOMER_COMPANY				= 'DPDPOLAND_CUSTOMER_COMPANY';
	const CUSTOMER_NAME					= 'DPDPOLAND_CUSTOMER_NAME';
	const CUSTOMER_PHONE				= 'DPDPOLAND_CUSTOMER_PHONE';
	const CUSTOMER_FID					= 'DPDPOLAND_CUSTOMER_FID';
	const MASTER_FID					= 'DPDPOLAND_MASTER_FID';

	const FILE_NAME 					= 'Configuration';
	const MEASUREMENT_ROUND_VALUE		= 6;
	const COD_MODULE_PREFIX				= 'DPDPOLAND_COD_';
	const PRICE_CALCULATION_CSV			= 'csv_calculation';
	const PRICE_CALCULATION_PRESTASHOP	= 'prestashop_calculation';

	const PRINTOUT_FORMAT_A4			= 'A4';
	const PRINTOUT_FORMAT_LABEL			= 'LBL_PRINTER';

	public $login 						= '';
	public $password 					= '';
	public $client_number				= '';
	public $client_name					= '';
	public $company_name				= '';
	public $name_surname 				= '';
	public $address 					= '';
	public $postcode					= '';
	public $city 						= '';
	public $email 						= '';
	public $phone						= '';
	public $customer_name				= '';
	public $customer_company			= '';
	public $customer_phone				= '';
	public $customer_fid				= '';
	public $master_fid					= '';
	public $price_calculation_type		= self::PRICE_CALCULATION_PRESTASHOP;
	public $carrier_standard 			= 0;
	public $carrier_standard_cod 		= 0;
	public $carrier_classic 			= 0;
	public $weight_conversation_rate 	= 1;
	public $dimension_conversation_rate = 1;
	public $ws_url						= '';

	public function __construct()
	{
		$this->getSettings();
	}

	public static function saveConfiguration()
	{
		$success = true;

		$success &= Configuration::updateValue(self::LOGIN, Tools::getValue(self::LOGIN));
		$success &= Configuration::updateValue(self::PASSWORD, Tools::getValue(self::PASSWORD));
		$success &= Configuration::updateValue(self::CLIENT_NUMBER, Tools::getValue(self::CLIENT_NUMBER));

		$client_name = DB::getInstance()->getValue('
			SELECT `name`
			FROM `'._DB_PREFIX_._DPDPOLAND_PAYER_NUMBERS_DB_.'`
			WHERE `payer_number` = "'.pSQL(Configuration::get(self::CLIENT_NUMBER)).'"
				AND `id_shop` = "'.(int)Context::getContext()->shop->id.'"
		');

		$success &= Configuration::updateValue(self::CLIENT_NAME, $client_name);
		$success &= Configuration::updateValue(self::COMPANY_NAME, Tools::getValue(self::COMPANY_NAME));
		$success &= Configuration::updateValue(self::NAME_SURNAME, Tools::getValue(self::NAME_SURNAME));
		$success &= Configuration::updateValue(self::ADDRESS, Tools::getValue(self::ADDRESS));
		$success &= Configuration::updateValue(self::POSTCODE, Tools::getValue(self::POSTCODE));
		$success &= Configuration::updateValue(self::CITY, Tools::getValue(self::CITY));
		$success &= Configuration::updateValue(self::EMAIL, Tools::getValue(self::EMAIL));
		$success &= Configuration::updateValue(self::PHONE, Tools::getValue(self::PHONE));
		$success &= Configuration::updateValue(self::CUSTOMER_COMPANY, Tools::getValue(self::CUSTOMER_COMPANY));
		$success &= Configuration::updateValue(self::CUSTOMER_NAME, Tools::getValue(self::CUSTOMER_NAME));
		$success &= Configuration::updateValue(self::CUSTOMER_PHONE, Tools::getValue(self::CUSTOMER_PHONE));
		$success &= Configuration::updateValue(self::CUSTOMER_FID, Tools::getValue(self::CUSTOMER_FID));
		$success &= Configuration::updateValue(self::MASTER_FID, Tools::getValue(self::MASTER_FID));
		$success &= Configuration::updateValue(self::PRICE_CALCULATION_TYPE, Tools::getValue(self::PRICE_CALCULATION_TYPE));
		$success &= Configuration::updateValue(self::CARRIER_STANDARD, (int)Tools::isSubmit(self::CARRIER_STANDARD));
		$success &= Configuration::updateValue(self::CARRIER_STANDARD_COD, (int)Tools::isSubmit(self::CARRIER_STANDARD_COD));
		$success &= Configuration::updateValue(self::CARRIER_CLASSIC, (int)Tools::isSubmit(self::CARRIER_CLASSIC));
		$success &= Configuration::updateValue(self::WEIGHT_CONVERSATION_RATE, Tools::getValue(self::WEIGHT_CONVERSATION_RATE));
		$success &= Configuration::updateValue(self::DIMENSION_CONVERSATION_RATE, Tools::getValue(self::DIMENSION_CONVERSATION_RATE));
		$success &= Configuration::updateValue(self::WS_URL, Tools::getValue(self::WS_URL));

		foreach (DpdPoland::getPaymentModules() as $payment_module)
			$success &= Configuration::updateValue(
				self::COD_MODULE_PREFIX.$payment_module['name'], (int)Tools::isSubmit(self::COD_MODULE_PREFIX.$payment_module['name'])
			);

		return $success;
	}

	private function getSettings()
	{
		$this->login = $this->getSetting(self::LOGIN, $this->login);
		$this->password = $this->getSetting(self::PASSWORD, $this->password);
		$this->client_number = $this->getSetting(self::CLIENT_NUMBER, $this->client_number);
		$this->client_name = $this->getSetting(self::CLIENT_NAME, $this->client_name);
		$this->company_name = $this->getSetting(self::COMPANY_NAME, $this->company_name);
		$this->name_surname = $this->getSetting(self::NAME_SURNAME, $this->name_surname);
		$this->address = $this->getSetting(self::ADDRESS, $this->address);
		$this->postcode = $this->getSetting(self::POSTCODE, $this->postcode);
		$this->city = $this->getSetting(self::CITY, $this->city);
		$this->email = $this->getSetting(self::EMAIL, $this->email);
		$this->phone = $this->getSetting(self::PHONE, $this->phone);
		$this->customer_company = $this->getSetting(self::CUSTOMER_COMPANY, $this->customer_company);
		$this->customer_name = $this->getSetting(self::CUSTOMER_NAME, $this->customer_name);
		$this->customer_phone = $this->getSetting(self::CUSTOMER_PHONE, $this->customer_phone);
		$this->customer_fid = $this->getSetting(self::CUSTOMER_FID, $this->customer_fid);
		$this->master_fid = $this->getSetting(self::MASTER_FID, $this->master_fid);
		$this->price_calculation_type = $this->getSetting(self::PRICE_CALCULATION_TYPE, $this->price_calculation_type);
		$this->carrier_standard = $this->getSetting(self::CARRIER_STANDARD, $this->carrier_standard);
		$this->carrier_standard_cod = $this->getSetting(self::CARRIER_STANDARD_COD, $this->carrier_standard_cod);
		$this->carrier_classic = $this->getSetting(self::CARRIER_CLASSIC, $this->carrier_classic);
		$this->weight_conversation_rate = $this->getSetting(self::WEIGHT_CONVERSATION_RATE, $this->weight_conversation_rate);
		$this->dimension_conversation_rate = $this->getSetting(self::DIMENSION_CONVERSATION_RATE, $this->dimension_conversation_rate);
		$this->ws_url = $this->getSetting(self::WS_URL, $this->ws_url);
	}

	private function getSetting($name, $default_value)
	{
		return Configuration::get($name) !== false ? Configuration::get($name) : $default_value;
	}

	public static function deleteConfiguration()
	{
		$success = true;

		$success &= self::deleteByNames(array(
			self::LOGIN, self::PASSWORD, self::CLIENT_NUMBER, self::CLIENT_NAME, self::COMPANY_NAME, self::NAME_SURNAME, self::ADDRESS,
			self::POSTCODE, self::CITY, self::EMAIL, self::PHONE, self::CUSTOMER_COMPANY, self::CUSTOMER_NAME, self::CUSTOMER_PHONE,
			self::CUSTOMER_FID, self::MASTER_FID, self::PRICE_CALCULATION_TYPE, self::CARRIER_STANDARD, self::CARRIER_STANDARD_COD,
			self::CARRIER_CLASSIC, self::WEIGHT_CONVERSATION_RATE, self::DIMENSION_CONVERSATION_RATE, self::WS_URL, self::CARRIER_STANDARD_ID,
			self::CARRIER_STANDARD_COD_ID, self::CARRIER_CLASSIC_ID
		));

		foreach (DpdPoland::getPaymentModules() as $payment_module)
			$success &= Configuration::deleteByName(self::COD_MODULE_PREFIX.$payment_module['name']);

		return $success;
	}
	
	private static function deleteByNames($names)
	{
		$success = true;

		foreach ($names as $name)
			$success &= Configuration::deleteByName($name);

		return $success;
	}

	public static function deleteByName($name)
	{
		return Configuration::deleteByName($name);
	}

	public static function checkRequiredConfiguration()
	{
		$configuration_obj = new DpdPolandConfiguration();

		if (!$configuration_obj->login ||
			!$configuration_obj->password ||
			!$configuration_obj->client_number ||
			!$configuration_obj->client_name ||
			!$configuration_obj->company_name ||
			!$configuration_obj->name_surname ||
			!$configuration_obj->address ||
			!$configuration_obj->postcode ||
			!$configuration_obj->city ||
			!$configuration_obj->email ||
			!$configuration_obj->phone ||
			!$configuration_obj->weight_conversation_rate ||
			!$configuration_obj->dimension_conversation_rate ||
			!$configuration_obj->ws_url ||
			!$configuration_obj->customer_company ||
			!$configuration_obj->customer_name ||
			!$configuration_obj->customer_phone ||
			!$configuration_obj->customer_fid ||
			!$configuration_obj->master_fid)
			return false;

		return true;	
	}
}