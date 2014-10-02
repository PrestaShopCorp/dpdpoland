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

class DpdPolandConfigurationController extends DpdPolandController
{
	public $available_services_ids = array();

	const SETTINGS_SAVE_ACTION = 'saveModuleSettings';
	const FILENAME = 'configuration.controller';

	public function getSettingsPage()
	{
		$configuration_obj = new DpdPolandConfiguration();

		$payment_modules = array();
		foreach (DpdPoland::getPaymentModules() as $payment_module)
		{
			$module = Module::getInstanceByName($payment_module['name']);

			if (!Validate::isLoadedObject($module))
				continue;

			$payment_modules[] = array(
				'displayName' => $module->displayName,
				'name' => $payment_module['name']
			);
		}

		$this->context->smarty->assign(array(
			'saveAction' => $this->module_instance->module_url,
			'settings' => $configuration_obj,
			'payer_numbers' => DpdPolandPayerNumber::getPayerNumbers(),
			'payment_modules' => $payment_modules,
			'zones' => Zone::getZones(),
			'carrier_zones' => $this->getZonesForCarriers()
		));

		if (version_compare(_PS_VERSION_, '1.6', '>='))
			return $this->context->smarty->fetch(_DPDPOLAND_TPL_DIR_.'admin/configuration_16.tpl');

		return $this->context->smarty->fetch(_DPDPOLAND_TPL_DIR_.'admin/configuration.tpl');
	}

	private function getZonesForCarriers()
	{
		require_once(_DPDPOLAND_CONTROLLERS_DIR_.'service.php');

		$id_carrier_classic = (int)Configuration::get(DpdPolandConfiguration::CARRIER_CLASSIC_ID);
		$id_carrier_standard = (int)Configuration::get(DpdPolandConfiguration::CARRIER_STANDARD_ID);
		$id_carrier_standard_cod = (int)Configuration::get(DpdPolandConfiguration::CARRIER_STANDARD_COD_ID);

		$classic_carrier_obj = DpdPolandService::getCarrierById((int)$id_carrier_classic);
		$carrier_standard_obj = DpdPolandService::getCarrierById((int)$id_carrier_standard);
		$carrier_standard_cod_obj = DpdPolandService::getCarrierById((int)$id_carrier_standard_cod);

		if (Validate::isLoadedObject($classic_carrier_obj))
			$id_carrier_classic = $classic_carrier_obj->id;

		if (Validate::isLoadedObject($carrier_standard_obj))
			$id_carrier_standard = $carrier_standard_obj->id;

		if (Validate::isLoadedObject($carrier_standard_cod_obj))
			$id_carrier_standard_cod = $carrier_standard_cod_obj->id;

		$carrier_classic_zones = DpdPolandConfiguration::getCarrierZones((int)$id_carrier_classic);
		$carrier_standard_zones = DpdPolandConfiguration::getCarrierZones((int)$id_carrier_standard);
		$carrier_standard_cod_zones = DpdPolandConfiguration::getCarrierZones((int)$id_carrier_standard_cod);

		$carrier_classic_zones_array = array();
		$carrier_standard_zones_array = array();
		$carrier_standard_cod_zones_array = array();

		foreach ($carrier_classic_zones as $zone)
			$carrier_classic_zones_array[] = $zone['id_zone'];

		foreach ($carrier_standard_zones as $zone)
			$carrier_standard_zones_array[] = $zone['id_zone'];

		foreach ($carrier_standard_cod_zones as $zone)
			$carrier_standard_cod_zones_array[] = $zone['id_zone'];

		return array(
			'classic' => $carrier_classic_zones_array,
			'standard' => $carrier_standard_zones_array,
			'standard_cod' => $carrier_standard_cod_zones_array
		);
	}

	public static function init()
	{
		$controller = new DpdPolandConfigurationController;

		if (Tools::isSubmit(self::SETTINGS_SAVE_ACTION))
		{
			$controller->validateSettings();
			$controller->createDeleteCarriers();

			if (!self::$errors)
				$controller->saveSettings();
			else
				$controller->module_instance->outputHTML($controller->module_instance->displayErrors(self::$errors));
		}
	}

	private function createDeleteCarriers()
	{
		require_once(_DPDPOLAND_CONTROLLERS_DIR_.'service.php');
		require_once(_DPDPOLAND_CONTROLLERS_DIR_.'dpd_classic.service.php');
		require_once(_DPDPOLAND_CONTROLLERS_DIR_.'dpd_standard.service.php');
		require_once(_DPDPOLAND_CONTROLLERS_DIR_.'dpd_standard_cod.service.php');

		if (Tools::getValue(DpdPolandConfiguration::CARRIER_CLASSIC))
		{
			if (!DpdPolandCarrierClassicService::install())
				self::$errors[] = $this->l('Could not save DPD international shipment (DPD Classic) service');
		}
		else
		{
			if (!DpdPolandCarrierClassicService::delete())
				self::$errors[] = $this->l('Could not delete DPD international shipment (DPD Classic) service');
		}

		if (Tools::getValue(DpdPolandConfiguration::CARRIER_STANDARD))
		{
			if (!DpdPolandCarrierStandardService::install())
				self::$errors[] = $this->l('Could not save DPD domestic shipment - Standard service');
		}
		else
		{
			if (!DpdPolandCarrierStandardService::delete())
				self::$errors[] = $this->l('Could not delete DPD domestic shipment - Standard service');
		}

		if (Tools::getValue(DpdPolandConfiguration::CARRIER_STANDARD_COD))
		{
			if (!DpdPolandCarrierStandardCODService::install())
				self::$errors[] = $this->l('Could not save DPD domestic shipment - Standard with COD service');
		}
		else
		{
			if (!DpdPolandCarrierStandardCODService::delete())
				self::$errors[] = $this->l('Could not delete DPD domestic shipment - Standard with COD service');
		}
	}

	private function validateSettings()
	{
		if (!Tools::getValue(DpdPolandConfiguration::LOGIN))
			self::$errors[] = $this->l('Login can not be empty');

		if (!Tools::getValue(DpdPolandConfiguration::PASSWORD))
			self::$errors[] = $this->l('Password can not be empty');
		elseif (!Validate::isPasswd(Tools::getValue(DpdPolandConfiguration::PASSWORD)))
			self::$errors[] = $this->l('Password is not valid');

		if (!Tools::getValue(DpdPolandConfiguration::CLIENT_NUMBER))
			self::$errors[] = $this->l('Default client number must be set');

		if (!Tools::getValue(DpdPolandConfiguration::COMPANY_NAME))
			self::$errors[] = $this->l('Company name can not be empty');
		elseif (!Validate::isLabel(Tools::getValue(DpdPolandConfiguration::COMPANY_NAME)))
			self::$errors[] = $this->l('Company name is not valid');

		if (!Tools::getValue(DpdPolandConfiguration::NAME_SURNAME))
			self::$errors[] = $this->l('Name and Surname can not be empty');
		elseif (!Validate::isName(Tools::getValue(DpdPolandConfiguration::NAME_SURNAME)))
	self::$errors[] = $this->l('Name and surname are not valid. Please use only letters and separate first name from last name with white space.');

		if (!Tools::getValue(DpdPolandConfiguration::ADDRESS))
			self::$errors[] = $this->l('Address can not be empty');
		elseif (!Validate::isAddress(Tools::getValue(DpdPolandConfiguration::ADDRESS)))
			self::$errors[] = $this->l('Address is not valid');

		if (!Tools::getValue(DpdPolandConfiguration::POSTCODE))
			self::$errors[] = $this->l('Postal code not be empty');
		elseif (!Validate::isPostCode(Tools::getValue(DpdPolandConfiguration::POSTCODE)))
			self::$errors[] = $this->l('Postal code is not valid');

		if (!Tools::getValue(DpdPolandConfiguration::CITY))
			self::$errors[] = $this->l('City can not be empty');
		elseif (!Validate::isCityName(Tools::getValue(DpdPolandConfiguration::CITY)))
			self::$errors[] = $this->l('City is not valid');

		if (!Tools::getValue(DpdPolandConfiguration::EMAIL))
			self::$errors[] = $this->l('Email can not be empty');
		elseif (!Validate::isEmail(Tools::getValue(DpdPolandConfiguration::EMAIL)))
			self::$errors[] = $this->l('Email is not valid');

		if (!Tools::getValue(DpdPolandConfiguration::PHONE))
			self::$errors[] = $this->l('Tel. No. can not be empty');
		elseif (!Validate::isPhoneNumber(Tools::getValue(DpdPolandConfiguration::PHONE)))
			self::$errors[] = $this->l('Tel. No. is not valid');

		if (Tools::isSubmit(DpdPolandConfiguration::CARRIER_STANDARD_COD))
		{
			$checked = false;

			foreach (DpdPoland::getPaymentModules() as $payment_module)
				if (Tools::isSubmit(DpdPolandConfiguration::COD_MODULE_PREFIX.$payment_module['name']))
					$checked = true;

			if (!$checked)
				self::$errors[] = $this->l('At least one COD payment method must be checked');
		}

		if (!Tools::getValue(DpdPolandConfiguration::WEIGHT_CONVERSATION_RATE))
			self::$errors[] = $this->l('Weight conversation rate can not be empty');
		elseif (!Validate::isUnsignedFloat(Tools::getValue(DpdPolandConfiguration::WEIGHT_CONVERSATION_RATE)))
			self::$errors[] = $this->l('Weight conversation rate is not valid');

		if (!Tools::getValue(DpdPolandConfiguration::DIMENSION_CONVERSATION_RATE))
			self::$errors[] = $this->l('Dimension conversation rate can not be empty');
		elseif (!Validate::isUnsignedFloat(Tools::getValue(DpdPolandConfiguration::DIMENSION_CONVERSATION_RATE)))
			self::$errors[] = $this->l('Dimension conversation rate is not valid');

		if (!Tools::getValue(DpdPolandConfiguration::CUSTOMER_COMPANY))
			self::$errors[] = $this->l('Customer company name can not be empty');
		elseif (!Validate::isLabel(Tools::getValue(DpdPolandConfiguration::CUSTOMER_COMPANY)))
			self::$errors[] = $this->l('Customer company name is not valid');

		if (!Tools::getValue(DpdPolandConfiguration::CUSTOMER_NAME))
			self::$errors[] = $this->l('Customer name and surname can not be empty');
		elseif (!Validate::isName(Tools::getValue(DpdPolandConfiguration::CUSTOMER_NAME)))
			self::$errors[] = $this->l('Customer name and surname is not valid');

		if (!Tools::getValue(DpdPolandConfiguration::CUSTOMER_PHONE))
			self::$errors[] = $this->l('Customer tel. No. can not be empty');
		elseif (!Validate::isPhoneNumber(Tools::getValue(DpdPolandConfiguration::CUSTOMER_PHONE)))
			self::$errors[] = $this->l('Customer tel. No. is not valid');

		if (!Tools::getValue(DpdPolandConfiguration::CUSTOMER_FID))
			self::$errors[] = $this->l('Customer FID can not be empty');
		elseif (!ctype_alnum(Tools::getValue(DpdPolandConfiguration::CUSTOMER_FID)))
			self::$errors[] = $this->l('Customer FID is not valid');

		if (!Tools::getValue(DpdPolandConfiguration::MASTER_FID))
			self::$errors[] = $this->l('Master FID can not be empty');
		elseif (!ctype_alnum(Tools::getValue(DpdPolandConfiguration::MASTER_FID)))
			self::$errors[] = $this->l('Master FID is not valid');

		if (!Tools::getValue(DpdPolandConfiguration::WS_URL))
			self::$errors[] = $this->l('Web Services URL can not be empty');
		elseif (!Validate::isUrl(Tools::getValue(DpdPolandConfiguration::WS_URL)))
			self::$errors[] = $this->l('Web Services URL is not valid');
	}

	private function saveSettings()
	{
		if (DpdPolandConfiguration::saveConfiguration())
		{
			if (!DpdPolandConfiguration::saveZonesForCarriers())
				DpdPoland::addFlashError($this->l('Settings saved successfully but could not assign zones for carriers'));
			else
			{
				DpdPoland::addFlashMessage($this->l('Settings saved successfully'));
				Tools::redirectAdmin($this->module_instance->module_url.'&menu=configuration');
			}
		}
		else
			DpdPoland::addFlashError($this->l('Could not save settings'));
	}
}