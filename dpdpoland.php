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

if (!function_exists('bqSQL'))
{
	function bqSQL($string)
	{
		return str_replace('`', '\`', pSQL($string));
	}
}

require_once(dirname(__FILE__).'/config.api.php');
require_once(_DPDPOLAND_CLASSES_DIR_.'controller.php');
require_once(dirname(__FILE__).'/dpdpoland.ws.php');
require_once(_DPDPOLAND_CLASSES_DIR_.'messages.controller.php');
require_once(_DPDPOLAND_CLASSES_DIR_.'configuration.controller.php');

require_once(_DPDPOLAND_MODELS_DIR_.'ObjectModel.php');
require_once(_DPDPOLAND_MODELS_DIR_.'CSV.php');
require_once(_DPDPOLAND_MODELS_DIR_.'Configuration.php');
require_once(_DPDPOLAND_MODELS_DIR_.'Package.php');
require_once(_DPDPOLAND_MODELS_DIR_.'Parcel.php');
require_once(_DPDPOLAND_MODELS_DIR_.'ParcelProduct.php');
require_once(_DPDPOLAND_MODELS_DIR_.'PayerNumber.php');
require_once(_DPDPOLAND_MODELS_DIR_.'Country.php');
require_once(_DPDPOLAND_MODELS_DIR_.'Pickup.php');
require_once(_DPDPOLAND_MODELS_DIR_.'Manifest.php');
require_once(_DPDPOLAND_MODELS_DIR_.'Carrier.php');

if (version_compare(_PS_VERSION_, '1.5', '<'))
	require_once(dirname(__FILE__).'/backward_compatibility/backward.php');

class DpdPoland extends Module
{
	private $html = '';
	public $module_url;
	public static $errors = array();

	public $id_carrier; /*mandatory field for carrier recognision in front office*/
	private static $parcels = array(); /*used to cache parcel setup for price calculation in front office*/
	private static $carriers = array(); /*DPD carriers prices cache, used in front office*/

	const CURRENT_INDEX = 'index.php?tab=AdminModules&token=';
	const POLAND_ISO_CODE = 'PL';

	public function __construct()
	{
		$this->name = 'dpdpoland';
		$this->tab = 'shipping_logistics';
		$this->version = '0.7';
		$this->author = 'DPD Polska Sp. z o.o.';

		parent::__construct();

		$this->displayName = $this->l('DPD Polska Sp. z o.o.');
		$this->description = $this->l('DPD Polska Sp. z o.o. shipping module');

		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			$this->context = new Context;
			$this->smarty = $this->context->smarty;
			$this->context->smarty->assign('ps14', true);
		}

		if (defined('_PS_ADMIN_DIR_'))
			$this->module_url = self::CURRENT_INDEX.Tools::getValue('token').'&configure='.$this->name.
				'&tab_module='.$this->tab.'&module_name='.$this->name;

		$this->bootstrap = true;
	}

	public function install()
	{
		if (!extension_loaded('soap'))
			return false;

		$sql = '
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_._DPDPOLAND_PRICE_RULE_DB_.'` (
				`id_csv` int(11) NOT NULL AUTO_INCREMENT,
				`id_shop` int(11) NOT NULL,
				`date_add` datetime DEFAULT NULL,
				`date_upd` datetime DEFAULT NULL,
				`iso_country` varchar(255) NOT NULL,
				`weight_from` varchar(255) NOT NULL,
				`weight_to` varchar(255) NOT NULL,
				`parcel_price` float NOT NULL,
				`cod_price` varchar(255) NOT NULL,
				`id_carrier` varchar(11) NOT NULL,
				PRIMARY KEY (`id_csv`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8';

		if (!Db::getInstance()->execute($sql)) return false;

		$sql = '
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_._DPDPOLAND_PAYER_NUMBERS_DB_.'` (
				`id_dpdpoland_payer_number` int(11) NOT NULL AUTO_INCREMENT,
				`id_shop` int(11) NOT NULL,
				`payer_number` varchar(255) NOT NULL,
				`name` varchar(255) NOT NULL,
				`date_add` datetime NOT NULL,
				`date_upd` datetime NOT NULL,
				PRIMARY KEY (`id_dpdpoland_payer_number`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8';

		if (!Db::getInstance()->execute($sql)) return false;

		$sql = '
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_._DPDPOLAND_COUNTRY_DB_.'` (
				`id_dpdpoland_country` int(11) NOT NULL AUTO_INCREMENT,
				`id_shop` int(11) NOT NULL,
				`id_country` int(11) NOT NULL,
				`enabled` tinyint(1) NOT NULL,
				`date_add` datetime NOT NULL,
				`date_upd` datetime NOT NULL,
				PRIMARY KEY (`id_dpdpoland_country`,`id_shop`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8';

		if (!Db::getInstance()->execute($sql)) return false;

		$sql = '
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_._DPDPOLAND_MANIFEST_DB_.'` (
				`id_manifest` int(11) NOT NULL,
				`id_package` int(11) NOT NULL,
				`date_add` datetime NOT NULL,
				`date_upd` datetime NOT NULL,
				PRIMARY KEY (`id_package`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8';

		if (!Db::getInstance()->execute($sql)) return false;

		$sql = '
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_._DPDPOLAND_PACKAGE_DB_.'` (
				`id_package` int(11) NOT NULL,
				`id_order` int(10) NOT NULL,
				`sessionId` int(11) NOT NULL,
				`sessionType` varchar(50) NOT NULL,
				`payerNumber` varchar(255) NOT NULL,
				`id_address_sender` int(10) NOT NULL,
				`id_address_delivery` int(10) NOT NULL,
				`cod_amount` decimal(17,2) DEFAULT NULL,
				`declaredValue_amount` decimal(17,2) DEFAULT NULL,
				`ref1` varchar(255) DEFAULT NULL,
				`ref2` varchar(255) DEFAULT NULL,
				`additional_info` text,
				`labels_printed` tinyint(1) NOT NULL DEFAULT "0",
				`date_add` datetime NOT NULL,
				`date_upd` datetime NOT NULL,
				PRIMARY KEY (`id_package`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8';

		if (!Db::getInstance()->execute($sql)) return false;

		$sql = '
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_._DPDPOLAND_PARCEL_DB_.'` (
				`id_parcel` int(11) NOT NULL,
				`id_package` int(11) NOT NULL,
				`waybill` varchar(50) NOT NULL,
				`content` text NOT NULL,
				`weight` decimal(20,6) NOT NULL,
				`height` decimal(20,6) NOT NULL,
				`length` decimal(20,6) NOT NULL,
				`width` decimal(20,6) NOT NULL,
				`number` int(5) NOT NULL,
				`date_add` datetime NOT NULL,
				`date_upd` datetime NOT NULL,
				PRIMARY KEY (`id_parcel`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8';

		if (!Db::getInstance()->execute($sql)) return false;

		$sql = '
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_._DPDPOLAND_CARRIER_DB_.'` (
				`id_dpdpoland_carrier` int(10) NOT NULL AUTO_INCREMENT,
				`id_carrier` int(10) NOT NULL,
				`id_reference` int(10) NOT NULL,
				`date_add` datetime NOT NULL,
				`date_upd` datetime NOT NULL,
				PRIMARY KEY (`id_dpdpoland_carrier`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8';

		if (!Db::getInstance()->execute($sql)) return false;

		$sql = '
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_._DPDPOLAND_PARCEL_PRODUCT_DB_.'` (
				`id_parcel_product` int(10) NOT NULL AUTO_INCREMENT,
				`id_parcel` int(11) NOT NULL,
				`id_product` int(10) NOT NULL,
				`id_product_attribute` int(10) NOT NULL,
				`name` varchar(255) NOT NULL,
				`weight` decimal(20,6) NOT NULL,
				`date_add` datetime NOT NULL,
				`date_upd` datetime NOT NULL,
				PRIMARY KEY (`id_parcel_product`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8';

		if (!Db::getInstance()->execute($sql)) return false;

		$current_date = date('Y-m-d H:i:s');
		$shops = Shop::getShops();

		foreach (array_keys($shops) as $id_shop)
		{
			$sql = '
				INSERT INTO `'._DB_PREFIX_._DPDPOLAND_PRICE_RULE_DB_.'`
					(`id_shop`, `date_add`, `date_upd`, `iso_country`, `weight_from`, `weight_to`, `parcel_price`, `cod_price`, `id_carrier`)
				VALUES
					("'.(int)$id_shop.'", "'.pSQL($current_date).'", "'.pSQL($current_date).'", "PL", "0", "0.5", "0", "", "'.
						(int)_DPDPOLAND_STANDARD_ID_.'"),
					("'.(int)$id_shop.'", "'.pSQL($current_date).'", "'.pSQL($current_date).'", "PL", "0", "0.5", "0", "0", "'.
						(int)_DPDPOLAND_STANDARD_COD_ID_.'"),
					("'.(int)$id_shop.'", "'.pSQL($current_date).'", "'.pSQL($current_date).'", "GB", "0", "0.5", "0", "", "'.
						(int)_DPDPOLAND_CLASSIC_ID_.'"),
					("'.(int)$id_shop.'", "'.pSQL($current_date).'", "'.pSQL($current_date).'", "*", "0", "0.5", "0", "", "'.
						(int)_DPDPOLAND_CLASSIC_ID_.'")
				';

			if (!Db::getInstance()->execute($sql)) return false;
		}

		if (!parent::install() || !$this->registerHook('adminOrder'))
			return false;

		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			if (!$this->registerHook('paymentTop'))
				return false;

			if (!$this->registerHook('updateCarrier'))
				return false;
		}
		else
			if (!$this->registerHook('paymentTop'))
				return false;

		require_once(_DPDPOLAND_CLASSES_DIR_.'countryList.controller.php');
		if (!DpdPolandCountryListController::disableDefaultCountries())
			return false;

		return true;
	}

	public function uninstall()
	{
		require_once(_DPDPOLAND_CLASSES_DIR_.'service.php');
		require_once(_DPDPOLAND_CLASSES_DIR_.'dpd_classic.service.php');
		require_once(_DPDPOLAND_CLASSES_DIR_.'dpd_standard.service.php');
		require_once(_DPDPOLAND_CLASSES_DIR_.'dpd_standard_cod.service.php');

		return
			parent::uninstall() &&
			DpdPolandCarrierClassicService::delete() &&
			DpdPolandCarrierStandardService::delete() &&
			DpdPolandCarrierStandardCODService::delete() &&
			DpdPolandConfiguration::deleteConfiguration() &&
			$this->dropTables() &&
			Configuration::deleteByName(DpdPolandWS::DEBUG_FILENAME);
	}

	private function dropTables()
	{
		return DB::getInstance()->Execute('
			DROP TABLE IF EXISTS
				`'._DB_PREFIX_._DPDPOLAND_PRICE_RULE_DB_.'`,
				`'._DB_PREFIX_._DPDPOLAND_PAYER_NUMBERS_DB_.'`,
				`'._DB_PREFIX_._DPDPOLAND_COUNTRY_DB_.'`,
				`'._DB_PREFIX_._DPDPOLAND_MANIFEST_DB_.'`,
				`'._DB_PREFIX_._DPDPOLAND_PACKAGE_DB_.'`,
				`'._DB_PREFIX_._DPDPOLAND_PARCEL_DB_.'`,
				`'._DB_PREFIX_._DPDPOLAND_PARCEL_PRODUCT_DB_.'`,
				`'._DB_PREFIX_._DPDPOLAND_CARRIER_DB_.'`
		');
	}

	private function soapClientExists()
	{
		return (bool)class_exists('SoapClient');
	}

	public function getContent()
	{
		if (!$this->soapClientExists())
			return $this->adminDisplayWarning($this->l('SoapClient class is missing'));

		if (_DPDPOLAND_DEBUG_MODE_)
			$this->displayDebugInfo();

		$this->displayFlashMessagesIfIsset();

		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			$this->addJS(_DPDPOLAND_JS_URI_.'backoffice.js');
			$this->addCSS(_DPDPOLAND_CSS_URI_.'backoffice.css');
			$this->addCSS(_DPDPOLAND_CSS_URI_.'toolbar.css');
		}
		else
		{
			$this->context->controller->addJS(_DPDPOLAND_JS_URI_.'backoffice.js');
			$this->context->controller->addCSS(_DPDPOLAND_CSS_URI_.'backoffice.css');
		}

		$this->setGlobalVariablesForAjax();
		$this->html .= $this->context->smarty->fetch(_DPDPOLAND_TPL_DIR_.'admin/global_variables.tpl');

		$country_currency_warning_message_text = $this->l('PL country and PLN currency must be installed; CURL must be enabled');
		$configuration_warning_message_text = $this->l('Module is not configured yet. Please check required settings');

		switch (Tools::getValue('menu'))
		{
			case 'arrange_pickup':
				$this->addDateTimePickerPlugins();
				require_once(_DPDPOLAND_CLASSES_DIR_.'arrange_pickup.controller.php');

				DpdPolandArrangePickUpController::init($this);
				$this->context->smarty->assign('breadcrumb', array($this->displayName, $this->l('Arrange PickUp')));
				$this->displayNavigation();

				if (!$this->checkModuleAvailability())
					return $this->html .= $this->displayWarnings(array($country_currency_warning_message_text));

				if (!DpdPolandConfiguration::checkRequiredConfiguration())
					return $this->html .= $this->displayWarnings(array($configuration_warning_message_text));

				$puckup_controller = new DpdPolandArrangePickUpController();
				$this->html .= $puckup_controller->getPage();
				break;
			case 'configuration':
				require_once(_DPDPOLAND_CLASSES_DIR_.'configuration.controller.php');
				DpdPolandConfigurationController::init();

				$this->context->smarty->assign('breadcrumb', array($this->displayName, $this->l('Settings')));
				$this->displayNavigation();

				if (!$this->checkModuleAvailability())
					return $this->html .= $this->displayWarnings(array($country_currency_warning_message_text));

				if (!DpdPolandConfiguration::checkRequiredConfiguration())
					$this->html .= $this->displayWarnings(array($configuration_warning_message_text));
				if (!version_compare(_PS_VERSION_, '1.5', '<'))
					$this->displayShopRestrictionWarning();

				$configuration_controller = new DpdPolandConfigurationController();
				$this->html .= $configuration_controller->getSettingsPage();
				break;
			case 'csv':
				require_once(_DPDPOLAND_CLASSES_DIR_.'csv.controller.php');
				DpdPolandCSVController::init();

				$this->context->smarty->assign('breadcrumb', array($this->displayName, $this->l('CSV prices import')));
				$this->displayNavigation();

				if (!$this->checkModuleAvailability())
					return $this->html .= $this->displayWarnings(array($country_currency_warning_message_text));

				if (!DpdPolandConfiguration::checkRequiredConfiguration())
					return $this->html .= $this->displayWarnings(array($configuration_warning_message_text));

				if (!version_compare(_PS_VERSION_, '1.5', '<') && Shop::getContext() != Shop::CONTEXT_SHOP)
				{
					$this->html .= $this->displayWarnings(array(
						$this->l('CSV management is disabled when all shops or group of shops are selected')));
					break;
				}
				$csv_controller = new DpdPolandCSVController();
				$this->html .= $csv_controller->getCSVPage();
				break;
			case 'help':
				$this->context->smarty->assign('breadcrumb', array($this->displayName, $this->l('Help')));
				$this->displayNavigation();
				$this->html .= $this->displayHelp();
				break;
			case 'manifest_list':
				$this->context->smarty->assign('breadcrumb', array($this->displayName, $this->l('Manifest list')));
				$this->displayNavigation();

				if (!$this->checkModuleAvailability())
					return $this->html .= $this->displayWarnings(array($country_currency_warning_message_text));

				if (!DpdPolandConfiguration::checkRequiredConfiguration())
					return $this->html .= $this->displayWarnings(array($configuration_warning_message_text));

				if (!version_compare(_PS_VERSION_, '1.5', '<') && Shop::getContext() != Shop::CONTEXT_SHOP)
				{
					$this->html .= $this->displayWarnings(array(
						$this->l('Manifests functionality is disabled when all shops or group of shops are chosen')));
					break;
				}

				$this->addDateTimePickerPlugins();
				require_once(_DPDPOLAND_CLASSES_DIR_.'manifestList.controller.php');
				$manifest_list_controller = new DpdPolandManifestListController();
				$this->html .= $manifest_list_controller->getListHTML();
				break;
			case 'parcel_history_list':
				$this->context->smarty->assign('breadcrumb', array($this->displayName, $this->l('Parcels history')));
				$this->displayNavigation();

				if (!$this->checkModuleAvailability())
					return $this->html .= $this->displayWarnings(array($country_currency_warning_message_text));

				if (!DpdPolandConfiguration::checkRequiredConfiguration())
					return $this->html .= $this->displayWarnings(array($configuration_warning_message_text));

				if (!version_compare(_PS_VERSION_, '1.5', '<') && Shop::getContext() != Shop::CONTEXT_SHOP)
				{
					$this->html .= $this->displayWarnings(array(
						$this->l('Parcels functionality is disabled when all shops or group of shops are chosen')));
					break;
				}

				$this->addDateTimePickerPlugins();
				require_once(_DPDPOLAND_CLASSES_DIR_.'parcelHistoryList.controller.php');
				$parcel_history_list_controller = new DpdPolandParcelHistoryController();
				$this->html .= $parcel_history_list_controller->getList();
				break;
			case 'country_list':
				$this->context->smarty->assign('breadcrumb', array($this->displayName, $this->l('Shipment countries')));
				$this->displayNavigation();

				if (!$this->checkModuleAvailability())
					return $this->html .= $this->displayWarnings(array($country_currency_warning_message_text));

				if (!DpdPolandConfiguration::checkRequiredConfiguration())
					return $this->html .= $this->displayWarnings(array($configuration_warning_message_text));

				if (!version_compare(_PS_VERSION_, '1.5', '<') && Shop::getContext() != Shop::CONTEXT_SHOP)
				{
					$this->html .= $this->displayWarnings(array(
						$this->l('Countries functionality is disabled when all shops or group of shops are chosen')));
					break;
				}

				require_once(_DPDPOLAND_CLASSES_DIR_.'countryList.controller.php');
				$country_list_controller = new DpdPolandCountryListController();
				$this->html .= $country_list_controller->getListHTML();
				break;
			case 'packages_list':
			default:
				$this->context->smarty->assign('breadcrumb', array($this->displayName, $this->l('Packages')));
				$this->displayNavigation();

				if (!$this->checkModuleAvailability())
					return $this->html .= $this->displayWarnings(array($country_currency_warning_message_text));

				if (!DpdPolandConfiguration::checkRequiredConfiguration())
					return $this->html .= $this->displayWarnings(array($configuration_warning_message_text));

				if (!version_compare(_PS_VERSION_, '1.5', '<') && Shop::getContext() != Shop::CONTEXT_SHOP)
				{
					$this->html .= $this->displayWarnings(array(
						$this->l('Packages functionality is disabled when all shops or group of shops are chosen')));
					break;
				}

				$this->addDateTimePickerPlugins();
				require_once(_DPDPOLAND_CLASSES_DIR_.'packageList.controller.php');

				DpdPolandPackageListController::init($this);
				$package_list_controller = new DpdPolandPackageListController();
				$this->html .= $package_list_controller->getList();
				break;
		}

		return $this->html;
	}

	private function displayDebugInfo()
	{
		$warning_message = $this->l('Module is in DEBUG mode');

		if (Configuration::get(DpdPolandWS::DEBUG_FILENAME))
		{
			if (version_compare(_PS_VERSION_, '1.5', '<'))
				$warning_message .= $this->l(', file:').' '._DPDPOLAND_MODULE_URI_.Configuration::get(DpdPolandWS::DEBUG_FILENAME);
			else
				$warning_message .= '<br />
				<a target="_blank" href="'._DPDPOLAND_MODULE_URI_.Configuration::get(DpdPolandWS::DEBUG_FILENAME).'">
					'.$this->l('View debug file').'
				</a>';
		}

		if (version_compare(_PS_VERSION_, '1.5', '<'))
			$this->html .= $this->displayWarnings(array($warning_message));
		else
			$this->adminDisplayWarning($warning_message);
	}

	private function displayHelp()
	{
		if (Tools::isSubmit('print_pdf'))
		{
			$filename = 'dpdpoland_eng.pdf';
			if (Tools::isSubmit('polish'))
				$filename = 'dpdpoland_pol.pdf';

			ob_end_clean();
			header('Content-type: application/pdf');
			header('Content-Disposition: attachment; filename="'.$this->l('manual').'.pdf"');
			readfile(_PS_MODULE_DIR_.'dpdpoland/manual/'.$filename);
			exit;
		}

		return $this->context->smarty->fetch(_DPDPOLAND_TPL_DIR_.'admin/help.tpl');
	}

	private function checkModuleAvailability()
	{
		return (bool)Country::getByIso(self::POLAND_ISO_CODE) &&
			(bool)Currency::getIdByIsoCode(_DPDPOLAND_CURRENCY_ISO_) && (bool)function_exists('curl_init');
	}

	/**
	* module configuration page
	* @return page HTML code
	*/

	private function setGlobalVariablesForAjax()
	{
		$this->context->smarty->assign(array(
			'dpdpoland_ajax_uri' => _DPDPOLAND_AJAX_URI_,
			'dpdpoland_pdf_uri' => _DPDPOLAND_PDF_URI_,
			'dpdpoland_token' => sha1(_COOKIE_KEY_.$this->name),
			'dpdpoland_id_shop' => (int)$this->context->shop->id,
			'dpdpoland_id_lang' => (int)$this->context->language->id
		));
	}

	public function savePackageFromPost()
	{
		$id_current_order = (int)Tools::getValue('id_order');
		$current_session_type = Tools::getValue('dpdpoland_SessionType');
		$id_method = (int)$this->getMethodBySessionType($current_session_type);

		if (!$this->validateAddressForPackageSession((int)Tools::getValue('dpdpoland_id_address_delivery'), (int)$id_method))
		{
			self::$errors[] = $this->l('Your develivery address is not compatible with the selected shipping method.').' '.
				$this->l('DPD Poland Domestic is available only if develivery address is Poland.').' '.
				$this->l('DPD Internationl shipping method is available only if delivery address is not Poland.');
			return false;
		}

		$address_delivery = new Address((int)Tools::getValue('dpdpoland_id_address_delivery'));
		$address_delivery->id = 0;
		$address_delivery->deleted = 1;

		if (!$address_delivery->save())
		{
			self::$errors[] = $this->l('Could not save client address');
			return false;
		}

		$configuration = new DpdPolandConfiguration();
		$address_sender = new Address();
		$id_country = Country::getByIso('PL');
		$country = new Country($id_country, $this->context->language->id);
		$address_sender->id_country = Country::getByIso(self::POLAND_ISO_CODE);
		$address_sender->company = $configuration->company_name;
		$address_sender->firstname = $configuration->name_surname;
		$address_sender->lastname = $configuration->name_surname;
		$address_sender->address1 = $configuration->address;
		$address_sender->address2 = $country->name;
		$address_sender->postcode = self::convertPostcode($configuration->postcode);
		$address_sender->city = $configuration->city;
		$address_sender->other = $configuration->email;
		$address_sender->phone = $configuration->phone;
		$address_sender->alias = $this->l('Sender address');
		$address_sender->deleted = 1;

		if (!$address_sender->save())
		{
			self::$errors[] = $this->l('Could not save sender address');
			return false;
		}

		$additional_info = Tools::getValue('additional_info');

		$package = new DpdPolandPackage;
		$package->id_order = $id_current_order;
		$package->sessionType = Tools::getValue('dpdpoland_SessionType');
		$package->payerNumber = Tools::getValue('dpdpoland_PayerNumber');
		$package->id_address_delivery = (int)$address_delivery->id;
		$package->id_address_sender = (int)$address_sender->id;
		$package->additional_info = $additional_info;
		$package->ref1 = Tools::getValue('dpdpoland_ref1');
		$package->ref2 = Tools::getValue('dpdpoland_ref2');

		if ($package->sessionType == 'domestic_with_cod')
			$package->cod_amount = (float)Tools::getValue('dpdpoland_COD_amount');

		if ($declaredValue_amount = Tools::getValue('dpdpoland_DeclaredValue_amount'))
			$package->declaredValue_amount = (float)$declaredValue_amount;

		foreach (Tools::getValue('parcels') as $parcel)
			$package->addParcel($parcel, $additional_info);

		if (!$result = $package->create())
		{
			self::$errors = DpdPolandPackage::$errors;
			return false;
		}
		elseif (!$this->saveParcelsIntoPackage($package->id_package,
			Tools::getValue('parcels'), $result['Parcels'], Tools::getValue('dpdpoland_products')))
			return false;

		$waybill = isset($result['Parcels']['Parcel']['Waybill']) ?
			$result['Parcels']['Parcel']['Waybill'] : $result['Parcels']['Parcel'][0]['Waybill'];
		if (!$this->addTrackingNumber($id_current_order, $waybill))
			return false;

		return $package->id_package;
	}

	private function getMethodBySessionType($session_type)
	{
		switch ($session_type)
		{
			case 'domestic':
				return _DPDPOLAND_STANDARD_ID_;
			case 'domestic_with_cod':
				return _DPDPOLAND_STANDARD_COD_ID_;
			case 'international':
				return _DPDPOLAND_CLASSIC_ID_;
			default:
				return false;
		}
	}

	private function validateAddressForPackageSession($id_address, $id_method)
	{
		$address = new Address($id_address);
		$poland_country = Country::getByIso(self::POLAND_ISO_CODE);

		if ($address->id_country == $poland_country && $id_method == _DPDPOLAND_CLASSIC_ID_ ||
			$address->id_country != $poland_country && $id_method == _DPDPOLAND_STANDARD_ID_ ||
			$address->id_country != $poland_country && $id_method == _DPDPOLAND_STANDARD_COD_ID_)
			return false;

		return true;
	}

	private function addTrackingNumber($id_order, $tracking_number)
	{
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			return $this->addShippingNumber($id_order, $tracking_number);

		$order = new Order((int)$id_order);
		$order_carrier = new OrderCarrier((int)$order->getIdOrderCarrier());
		if (!Validate::isLoadedObject($order_carrier))
		{
			self::$errors[] = $this->l('The order carrier ID is invalid.');
			return false;
		}
		elseif (!Validate::isTrackingNumber($tracking_number))
		{
			self::$errors[] = $this->l('The tracking number is incorrect.');
			return false;
		}
		else
		{
			$order->shipping_number = $tracking_number;
			$order->update();

			$order_carrier->tracking_number = $tracking_number;
			if ($order_carrier->update())
			{
				$customer = new Customer((int)$order->id_customer);
				$carrier = new Carrier((int)$order->id_carrier, $order->id_lang);
				if (!Validate::isLoadedObject($customer))
				{
					self::$errors[] = $this->l('Can\'t load Customer object');
					return false;
				}
				if (!Validate::isLoadedObject($carrier))
					return false;
				$templateVars = array(
					'{followup}' => str_replace('@', $order->shipping_number, $carrier->url),
					'{firstname}' => $customer->firstname,
					'{lastname}' => $customer->lastname,
					'{id_order}' => $order->id,
					'{shipping_number}' => $order->shipping_number,
					'{order_name}' => $order->getUniqReference()
				);

				if (@Mail::Send((int)$order->id_lang, 'in_transit', Mail::l('Package in transit', (int)$order->id_lang), $templateVars,
					$customer->email, $customer->firstname.' '.$customer->lastname, null, null, null, null,
					_PS_MAIL_DIR_, true, (int)$order->id_shop))
				{
					Hook::exec('actionAdminOrdersTrackingNumberUpdate', array('order' => $order, 'customer' => $customer, 'carrier' => $carrier));
					return true;
				}
				else
				{
					$this->addFlashError($this->l('An error occurred while sending an email to the customer.'));
					return true;
				}
			}
			else
			{
				self::$errors[] = $this->l('The order carrier cannot be updated.');
				return false;
			}
		}
	}

	private function addShippingNumber($id_order, $shipping_number)
	{
		$order = new Order((int)$id_order);

		$order->shipping_number = $shipping_number;
		$order->update();
		if ($shipping_number)
		{
			$customer = new Customer((int)$order->id_customer);
			$carrier = new Carrier((int)$order->id_carrier);
			if (!Validate::isLoadedObject($customer) || !Validate::isLoadedObject($carrier))
			{
				self::$errors[] = $this->l('Customer / Carrier not found');
				return false;
			}
			$templateVars = array(
				'{followup}' => str_replace('@', $order->shipping_number, $carrier->url),
				'{firstname}' => $customer->firstname,
				'{lastname}' => $customer->lastname,
				'{order_name}' => sprintf('#%06d', (int)$order->id),
				'{id_order}' => (int)$order->id
			);
			@Mail::Send((int)$order->id_lang, 'in_transit', Mail::l('Package in transit', (int)$order->id_lang), $templateVars,
				$customer->email, $customer->firstname.' '.$customer->lastname, null, null, null, null,
				_PS_MAIL_DIR_, true);
		}

		return true;
	}

	/*
	* $id_package - what package will parcels be saved to
	* $parcels - parcels data from POST
	* $parcels_ws - parcels data from webservices
	* $parcelProducts - parcels content
	**/
	private function saveParcelsIntoPackage($id_package, $parcels, $parcels_ws, $parcelProducts)
	{
		$parcels_ws = $parcels_ws['Parcel'];
		$parcels_ws = isset($parcels_ws[0]) ? $parcels_ws : array($parcels_ws); // array must be multidimentional

		foreach ($parcels_ws as $parcel_data)
		{
			list($order_reference, $parcel_number) = explode('_', $parcel_data['Reference']);

			if (!isset($parcels[$parcel_number]))
			{
				// parcel number received from ws does not match with any we have locally. Because of that we do not know what data should be saved
				self::$errors[] = sprintf($this->l('Parcel #%d does not exists'), $parcel_number);
				return false;
			}
			else
			{
				$parcel = new DpdPolandParcel;
				$parcel->id_parcel = (int)$parcel_data['ParcelId'];
				$parcel->id_package = (int)$id_package;
				$parcel->waybill = $parcel_data['Waybill'];
				$parcel->content = $parcels[$parcel_number]['content'];
				$parcel->weight = (float)$parcels[$parcel_number]['weight'];
				$parcel->height = (float)$parcels[$parcel_number]['height'];
				$parcel->length = (float)$parcels[$parcel_number]['length'];
				$parcel->width = (float)$parcels[$parcel_number]['width'];
				$parcel->number = (int)$parcel_number;

				if ($parcel->add())
					$this->saveProductsIntoParcel($parcel, $parcelProducts);
				else
					return false;
			}
		}

		return true;
	}

	/*
	* $parcel - object of parcel that products will be saved to
	* $products - parcels content
	**/
	private function saveProductsIntoParcel(DpdPolandParcel $parcel, $products)
	{
		foreach ($products as $product)
		{
			if ($product['parcel'] == $parcel->number) //product belongs to this parcel
			{
				$parcelProduct = new DpdPolandParcelProduct;
				$parcelProduct->id_parcel = (int)$parcel->id_parcel;
				$parcelProduct->id_product = (int)$product['id_product'];
				$parcelProduct->id_product_attribute = (int)$product['id_product_attribute'];
				$productObj = new Product((int)$product['id_product']);
				$combination = new Combination((int)$product['id_product_attribute']);
				$parcelProduct->name = (version_compare(_PS_VERSION_, '1.5', '<') ? $productObj->name[(int)Context::getContext()->language->id] :
					Product::getProductName($product['id_product'], $product['id_product_attribute']));
				$parcelProduct->weight = (float)$combination->weight + (float)$productObj->weight;

				if (!$parcelProduct->add())
				{
					self::$errors[] = sprintf($this->l('Unable to save product #%s to parcel #%d'), $parcelProduct->id_product.'-'.
						$parcelProduct->id_product_attribute, $parcelProduct->id_parcel);
					return false;
				}
			}
		}

		return true;
	}

	private function addDateTimePickerPlugins()
	{
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			return includeDatepicker(null);

		$this->context->controller->addJqueryUI(array(
			'ui.slider', // for datetimepicker
			'ui.datepicker' // for datetimepicker
		));

		$this->context->controller->addJS(array(
			_DPDPOLAND_JS_URI_.'jquery.bpopup.min.js',
			_PS_JS_DIR_.'jquery/plugins/timepicker/jquery-ui-timepicker-addon.js' // for datetimepicker
		));

		$this->addCSS(_PS_JS_DIR_.'jquery/plugins/timepicker/jquery-ui-timepicker-addon.css'); // for datetimepicker
	}

	private function displayShopRestrictionWarning()
	{
		if (Shop::getContext() == Shop::CONTEXT_GROUP)
			$this->html .= $this->displayWarnings(array(
				$this->l('You have chosen a group of shops, all the changes will be set for all shops in this group')));

		if (Shop::getContext() == Shop::CONTEXT_ALL)
			$this->html .= $this->displayWarnings(array($this->l('You have chosen all shops, all the changes will be set for all shops')));
	}

	public function outputHTML($html)
	{
		$this->html .= $html;
	}

	public static function addCSS($css_uri)
	{
		echo '<link href="'.$css_uri.'" rel="stylesheet" type="text/css">';
	}

	public static function addJS($js_uri)
	{
		echo '<script src="'.$js_uri.'" type="text/javascript"></script>';
	}

	private function displayNavigation()
	{
		if (version_compare(_PS_VERSION_, '1.6', '>='))
			$this->context->smarty->assign('meniutabs', $this->initNavigation16());

		$this->context->smarty->assign('module_link', $this->module_url);
		$this->html .= $this->context->smarty->fetch(_DPDPOLAND_TPL_DIR_.'admin/navigation.tpl');
	}

	private function initNavigation16()
	{
		$meniu_tabs = array(
			'arrange_pickup' => array(
				'short' => 'Arrange Pickup',
				'desc' => $this->l('Arrange Pickup'),
				'href' => $this->module_url.'&menu=arrange_pickup',
				'active' => false,
				'imgclass' => 'icon-calendar'
			),
			'packages_list' => array(
				'short' => 'Packages list',
				'desc' => $this->l('Packages list'),
				'href' => $this->module_url.'&menu=packages_list',
				'active' => false,
				'imgclass' => 'icon-list'
			),
			'manifest_list' => array(
				'short' => 'Manifest list',
				'desc' => $this->l('Manifest list'),
				'href' => $this->module_url.'&menu=manifest_list',
				'active' => false,
				'imgclass' => 'icon-th'
			),
			'parcel_history_list' => array(
				'short' => 'Parcels history',
				'desc' => $this->l('Parcels history'),
				'href' => $this->module_url.'&menu=parcel_history_list',
				'active' => false,
				'imgclass' => 'icon-history'
			),
			'country_list' => array(
				'short' => 'Shipment countries',
				'desc' => $this->l('Shipment countries'),
				'href' => $this->module_url.'&menu=country_list',
				'active' => false,
				'imgclass' => 'icon-globe'
			),
			'csv' => array(
				'short' => 'CSV prices import',
				'desc' => $this->l('CSV prices import'),
				'href' => $this->module_url.'&menu=csv',
				'active' => false,
				'imgclass' => 'icon-file'
			),
			'configuration' => array(
				'short' => 'Settings',
				'desc' => $this->l('Settings'),
				'href' => $this->module_url.'&menu=configuration',
				'active' => false,
				'imgclass' => 'icon-cogs'
			),
			'help' => array(
				'short' => 'Help',
				'desc' => $this->l('Help'),
				'href' => $this->module_url.'&menu=help',
				'active' => false,
				'imgclass' => 'icon-info-circle'
			),
		);

		$current_page = Tools::getValue('menu');

		if (in_array($current_page, array(
			'arrange_pickup',
			'packages_list',
			'manifest_list',
			'parcel_history_list',
			'country_list',
			'configuration',
			'help'
		)))
			$meniu_tabs[$current_page]['active'] = true;

		return $meniu_tabs;
	}

	/* adds success message into session */
	public static function addFlashMessage($msg)
	{
		$messages_controller = new DpdPolandMessagesController();
		$messages_controller->setSuccessMessage($msg);
	}

	public static function addFlashError($msg)
	{
		$messages_controller = new DpdPolandMessagesController();

		if (is_array($msg))
		{
			foreach ($msg as $message)
				$messages_controller->setErrorMessage($message);
		}
		else
			$messages_controller->setErrorMessage($msg);
	}

	/* displays success message only untill page reload */
	private function displayFlashMessagesIfIsset()
	{
		$messages_controller = new DpdPolandMessagesController();

		if ($success_message = $messages_controller->getSuccessMessage())
			$this->html .= $this->displayConfirmation($success_message);

		if ($error_message = $messages_controller->getErrorMessage())
			$this->html .= $this->displayErrors($error_message);
	}

	public function displayErrors($errors)
	{
		if (!is_array($errors))
			$errors = array($errors);
		$this->context->smarty->assign('errors', $errors);
		return $this->context->smarty->fetch(_DPDPOLAND_TPL_DIR_.'admin/errors.tpl');
	}

	public function displayWarnings($warnings)
	{
		$this->context->smarty->assign('warnings', $warnings);
		return $this->context->smarty->fetch(_DPDPOLAND_TPL_DIR_.'admin/warnings.tpl');
	}

	public static function getInputValue($name, $default_value = null)
	{
		return (Tools::isSubmit($name)) ? Tools::getValue($name) : $default_value;
	}

	public static function getMethodIdByCarrierId($id_carrier)
	{
		if (!$id_reference = self::getReferenceIdByCarrierId($id_carrier))
			return false;

		switch ($id_reference)
		{
			case Configuration::get(DpdPolandConfiguration::CARRIER_STANDARD_ID):
				return _DPDPOLAND_STANDARD_ID_;
			case Configuration::get(DpdPolandConfiguration::CARRIER_STANDARD_COD_ID):
				return _DPDPOLAND_STANDARD_COD_ID_;
			case Configuration::get(DpdPolandConfiguration::CARRIER_CLASSIC_ID):
				return _DPDPOLAND_CLASSIC_ID_;
			default:
				return false;
		}
	}

	public static function getReferenceIdByCarrierId($id_carrier)
	{
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			return DpdPolandCarrier::getReferenceByIdCarrier($id_carrier);

		return Db::getInstance()->getValue('
			SELECT `id_reference`
			FROM `'._DB_PREFIX_.'carrier`
			WHERE `id_carrier`='.(int)$id_carrier
		);
	}

	private function getSenderAddress($settings, $id_address = false)
	{
		if ($id_address)
		{
			$address = new Address((int)$id_address);
			return array(
				'company' => $address->company,
				'name' => $address->firstname,
				'street' => $address->address1,
				'postcode' => $address->postcode,
				'city' => $address->city,
				'country' => $address->address2,
				'email' => $address->other,
				'phone' => $address->phone
			);
		}

		$id_country = Country::getByIso('PL');
		$country = new Country($id_country, $this->context->language->id);

		return array(
			'company' => $settings->company_name,
			'name' => $settings->name_surname,
			'street' => $settings->address,
			'postcode' => self::convertPostcode($settings->postcode),
			'city' => $settings->city,
			'country' => $country->name,
			'email' => $settings->email,
			'phone' => $settings->phone
		);
	}

	private function getRecipientAddress($id_address)
	{
		$address = new Address((int)$id_address);
		$country = new Country((int)$address->id_country, $this->context->language->id);
		$customer = new Customer((int)$address->id_customer);

		return array(
			'company' => $address->company,
			'name' => $address->firstname.' '.$address->lastname,
			'street' => $address->address1,
			'postcode' => $address->postcode,
			'city' => $address->city,
			'country' => $country->name,
			'email' => $customer->email,
			'phone' => $address->phone ? $address->phone : $address->phone_mobile
		);
	}

	public function getFormatedAddressHTML($id_address)
	{
		$this->context->smarty->assign('address', $this->getRecipientAddress($id_address));
		return $this->context->smarty->fetch(_DPDPOLAND_TPL_DIR_.'admin/address.tpl');
	}

	public static function CODMethodIsAvailable()
	{
		return (bool)count(DpdPoland::getPaymentModules());
	}

	public function searchProducts($query)
	{
		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			$sql = '
				SELECT p.`id_product`, pl.`name`, p.`weight`
				FROM `'._DB_PREFIX_.'category_product` cp
				LEFT JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = cp.`id_product`)
				LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.`id_product` = p.`id_product` AND pl.`id_lang` = "'.
					(int)$this->context->language->id.'")
				WHERE pl.`name` LIKE \'%'.pSQL($query).'%\'
					OR p.`ean13` LIKE \'%'.pSQL($query).'%\'
					OR p.`upc` LIKE \'%'.pSQL($query).'%\'
					OR p.`reference` LIKE \'%'.pSQL($query).'%\'
					OR p.`supplier_reference` LIKE \'%'.pSQL($query).'%\'
				GROUP BY `id_product`
				ORDER BY pl.`name` ASC
			';
		}
		else
		{
			$sql = new DbQuery();
			$sql->select('p.`id_product`, pl.`name`, p.`weight`');
			$sql->from('category_product', 'cp');
			$sql->leftJoin('product', 'p', 'p.`id_product` = cp.`id_product`');
			$sql->join(Shop::addSqlAssociation('product', 'p'));
			$sql->leftJoin('product_lang', 'pl', '
				p.`id_product` = pl.`id_product`
				AND pl.`id_lang` = '.(int)$this->context->language->id.Shop::addSqlRestrictionOnLang('pl')
			);

			$where = 'pl.`name` LIKE \'%'.pSQL($query).'%\'
			OR p.`ean13` LIKE \'%'.pSQL($query).'%\'
			OR p.`upc` LIKE \'%'.pSQL($query).'%\'
			OR p.`reference` LIKE \'%'.pSQL($query).'%\'
			OR p.`supplier_reference` LIKE \'%'.pSQL($query).'%\'
			OR  p.`id_product` IN (SELECT id_product FROM '._DB_PREFIX_.'product_supplier sp WHERE `product_supplier_reference` LIKE \'%'.
				pSQL($query).'%\')';
			$sql->groupBy('`id_product`');
			$sql->orderBy('pl.`name` ASC');

			if (Combination::isFeatureActive())
			{
				$sql->leftJoin('product_attribute', 'pa', 'pa.`id_product` = p.`id_product`');
				$sql->join(Shop::addSqlAssociation('product_attribute', 'pa', false));
				$where .= ' OR pa.`reference` LIKE \'%'.pSQL($query).'%\'';
			}
			$sql->where($where);
		}

		$result = Db::getInstance()->executeS($sql);

		if (!$result)
			return array('found' => false, 'notfound' => $this->l('No product has been found.'));

		foreach ($result as &$product)
		{
			$product['id_product_attribute'] = Product::getDefaultAttribute($product['id_product']);
			$product['weight_numeric'] = $product['weight'];
			$product['weight'] = sprintf('%.3f', $product['weight']).' '._DPDPOLAND_DEFAULT_WEIGHT_UNIT_;
		}

		return array(
			'products' => $result,
			'found' => true
		);
	}

	public function addDPDClientNumber()
	{
		$number = Tools::getValue('client_number');
		$name = Tools::getValue('name');
		$id_shop = (int)Tools::getValue('id_shop', Context::getContext()->shop->id);
		$error = '';
		$success = '';

		if (!$number)
			$error .= $this->l('DPD client number is required').'<br />';
		elseif (!ctype_alnum($number))
			$error .= $this->l('DPD client number is not valid').'<br />';

		if (!$name)
			$error .= $this->l('Client name is required').'<br />';
		elseif (!Validate::isName($name))
			$error .= $this->l('Client name is not valid').'<br />';

		if (empty($error))
		{
			require_once(_DPDPOLAND_MODELS_DIR_.'PayerNumber.php');

			if (DpdPolandPayerNumber::payerNumberExists($number, $id_shop))
				$error .= $this->l('DPD client number already exists').'<br />';
			else
			{
				$payer_number_obj = new DpdPolandPayerNumber();
				$payer_number_obj->payer_number = $number;
				$payer_number_obj->name = $name;
				$payer_number_obj->id_shop = $id_shop;
				if (!$payer_number_obj->save())
					$error .= $this->l('DPD client number / name could not be saved').'<br />';
			}
		}

		$success = $this->l('DPD client number / name saved successfully');

		$return = array(
			'error' => $error,
			'message' => $success
		);

		return $return;
	}

	public function deleteDPDClientNumber()
	{
		$id_number = Tools::getValue('client_number');
		$error = '';
		$success = '';

		$configuration_obj = new DpdPolandConfiguration();

		$payer_number_obj = new DpdPolandPayerNumber((int)$id_number);
		$current_number = $payer_number_obj->payer_number;
		if (!$payer_number_obj->delete())
			$error .= $this->l('Could not delete DPD client number / name');

		if ($current_number == $configuration_obj->client_number)
			if (!DpdPolandConfiguration::deleteByName(DpdPolandConfiguration::CLIENT_NUMBER) ||
				!DpdPolandConfiguration::deleteByName(DpdPolandConfiguration::CLIENT_NAME))
				$error .= $this->l('Could not delete default client number setting');

		$success = $this->l('DPD client number / name deleted successfully');

		$return = array(
			'error' => $error,
			'message' => $success
		);

		return $return;
	}

	public static function getPaymentModules()
	{
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
				SELECT DISTINCT h.`id_hook`, m.`name`, hm.`position`
				FROM `'._DB_PREFIX_.'module_country` mc
				LEFT JOIN `'._DB_PREFIX_.'module` m ON m.`id_module` = mc.`id_module`
				INNER JOIN `'._DB_PREFIX_.'module_group` mg ON (m.`id_module` = mg.`id_module`)
				LEFT JOIN `'._DB_PREFIX_.'hook_module` hm ON hm.`id_module` = m.`id_module`
				LEFT JOIN `'._DB_PREFIX_.'hook` h ON hm.`id_hook` = h.`id_hook`
				WHERE h.`name` = \'payment\'
				AND m.`active` = 1
				ORDER BY hm.`position`, m.`name` DESC
			');
		return Module::getPaymentModules();
	}

	public function getPayerNumbersTableHTML()
	{
		$configuration_obj = new DpdPolandConfiguration();

		$this->context->smarty->assign(array(
			'settings' => $configuration_obj,
			'payer_numbers' => DpdPolandPayerNumber::getPayerNumbers()
		));

		return $this->context->smarty->fetch(_DPDPOLAND_TPL_DIR_.'admin/payer_numbers_table.tpl');
	}

	public function calculateTimeLeft()
	{
		$current_timeframe = Tools::getValue('timeframe');
		$current_date = Tools::getValue('date');

		if (!$current_timeframe)
			return false;

		$end_time = explode('-', $current_timeframe);
		if (!isset($end_time[1]))
			return 0;

		$end_time_in_seconds = strtotime($end_time[1]);
		$poland_time_obj = new DateTime(null, new DateTimeZone('Poland'));
		$poland_time_in_seconds = strtotime($poland_time_obj->format('H:i:s'));
		$days_left = strtotime($current_date) - strtotime(date('Y-m-d'));
		$time_left = round(($end_time_in_seconds + $days_left - $poland_time_in_seconds) / 60);

		if ($time_left < 0)
			$time_left = 0;

		return $time_left;
	}

	public function getTimeFrames()
	{
		require_once(_DPDPOLAND_CLASSES_DIR_.'arrange_pickup.controller.php');

		$current_date = Tools::getValue('date');

		$is_date_valid = true;

		if (!Validate::isDate($current_date))
		{
			DpdPolandPickup::$errors = array($this->l('Wrong date format'));
			$is_date_valid = false;
		}
		elseif (strtotime($current_date) < strtotime(date('Y-m-d')))
		{
			DpdPolandPickup::$errors = array($this->l('Date can not be earlier than').' '.date('Y-m-d'));
			$is_date_valid = false;
		}
		elseif (DpdPolandArrangePickUpController::isWeekend($current_date))
		{
			DpdPolandPickup::$errors = array($this->l('Weekends can not be chosen'));
			$is_date_valid = false;
		}

		if (!$is_date_valid)
		{
			$this->context->smarty->assign(array(
				'settings' => new DpdPolandConfiguration,
				'timeFrames' => false
			));

			return $this->context->smarty->fetch(_DPDPOLAND_TPL_DIR_.'admin/timeframes.tpl');
		}

		$pickup = new DpdPolandPickup;
		$is_today = (bool)date('Ymd') == date('Ymd', strtotime($current_date));
		$pickup_timeframes = $pickup->getCourierTimeframes();

		$poland_time_obj = new DateTime(null, new DateTimeZone('Poland'));
		$poland_time_in_seconds = strtotime($poland_time_obj->format('H:i:s'));

		DpdPolandArrangePickUpController::validateTimeframes($pickup_timeframes, $poland_time_in_seconds, $is_today);
		if (empty($pickup_timeframes))
		{
			DpdPolandPickup::$errors = array($this->l('No timeframes'));
			$pickup_timeframes = false;
		}

		$this->context->smarty->assign(array(
			'settings' => new DpdPolandConfiguration,
			'timeFrames' => $pickup_timeframes
		));

		$extra_timeframe = DpdPolandArrangePickUpController::createExtraTimeframe($pickup_timeframes);
		if ($extra_timeframe !== false)
			$this->context->smarty->assign('extra_timeframe', $extra_timeframe);

		return $this->context->smarty->fetch(_DPDPOLAND_TPL_DIR_.'admin/timeframes.tpl');
	}

	public static function convertWeight($weight)
	{
		if (!$conversation_rate = Configuration::get(DpdPolandConfiguration::WEIGHT_CONVERSATION_RATE))
			$conversation_rate = 1;

		return (float)$weight * (float)$conversation_rate;
	}

	public static function convertDimention($value)
	{
		if (!$conversation_rate = Configuration::get(DpdPolandConfiguration::DIMENSION_CONVERSATION_RATE))
			$conversation_rate = 1;

		return sprintf('%.6f', (float)$value * (float)$conversation_rate);
	}

	public static function convertPostcode($postcode)
	{
		return Tools::strtoupper(preg_replace('/[^a-zA-Z0-9]+/', '', $postcode));
	}

	public function hookAdminOrder($params)
	{
		if (!$this->soapClientExists())
			return '';

		$order = new Order((int)$params['id_order']);
		if (!DpdPolandConfiguration::checkRequiredConfiguration())
		{
			$this->context->smarty->assign(array(
				'displayBlock' => false,
				'moduleSettingsLink' => $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&menu=configuration'
			));
		}
		else
		{
			$this->displayFlashMessagesIfIsset(); // PDF error might be set as flash error
			$order = new Order((int)$params['id_order']);
			$package = DpdPolandPackage::getInstanceByIdOrder((int)$order->id);
			$parcels = DpdPolandParcel::getParcels($order, $package->id_package);
			$products = DpdPolandParcelProduct::getShippedProducts($order, DpdPolandParcelProduct::getProductDetailsByParcels($parcels));

			$customer = new Customer((int)$order->id_customer);
			$settings = new DpdPolandConfiguration;

			if (version_compare(_PS_VERSION_, '1.5', '<'))
			{
				$this->addJS(_PS_JS_DIR_.'jquery/jquery.scrollTo-1.4.2-min.js');
				$this->addJS(_PS_JS_DIR_.'jquery/jquery-ui-1.8.10.custom.min.js');
				$this->addJS(_PS_JS_DIR_.'jquery/accordion/accordion.ui.js');
				$this->addJS(_PS_JS_DIR_.'jquery/jquery.autocomplete.js');
				$this->addJS(_DPDPOLAND_JS_URI_.'adminOrder.js');
				$this->addCSS(_DPDPOLAND_CSS_URI_.'adminOrder.css');
				$this->addCSS(_PS_CSS_DIR_.'jquery-ui-1.8.10.custom.css');
			}
			else
			{
				$this->context->controller->addJqueryUI(array(
					'ui.core',
					'ui.widget',
					'ui.accordion'
				));

				$this->context->controller->addJqueryPlugin('scrollTo');

				$this->context->controller->addJS(_DPDPOLAND_JS_URI_.'adminOrder.js');
				$this->context->controller->addCSS(_DPDPOLAND_CSS_URI_.'adminOrder.css');
			}

			$selectedPayerNumber = ($package->payerNumber) ? $package->payerNumber : $settings->client_number;
			$selectedRecipientIdAddress = ($package->id_address_delivery) ? $package->id_address_delivery : $order->id_address_delivery;

			$id_currency_pl = Currency::getIdByIsoCode(_DPDPOLAND_CURRENCY_ISO_, (int)$this->context->shop->id);
			$currency_to = new Currency((int)$id_currency_pl);
			$currency_from = new Currency($order->id_currency);

			$id_method = $this->getMethodIdByCarrierId((int)$order->id_carrier);
			if ($id_method) // if order shipping method is one of DPD shipping methods
			{
				$payment_method_compatible = false;

				$is_cod_module = Configuration::get(DpdPolandConfiguration::COD_MODULE_PREFIX.$order->module);

				if ($id_method == _DPDPOLAND_STANDARD_COD_ID_ && $is_cod_module ||
					$id_method == _DPDPOLAND_STANDARD_ID_ && !$is_cod_module ||
					$id_method == _DPDPOLAND_CLASSIC_ID_ && !$is_cod_module)
					$payment_method_compatible = true;
			}
			else
				$payment_method_compatible = true;

			if (!$payment_method_compatible)
			{
				$error_message = $this->l('Your payment method and Shipping method is not compatible.').'<br />';
				$error_message .= ' '.$this->l('If delivery address is not Poland, then COD payment method is not supported.').'<br />';
				$error_message .= ' '.$this->l('If delivery address is not Poland, then COD payment method is not supported.').'<br />';
				$error_message .= ' '.$this->l('If delivery address is Poland and payment method is COD please use shipping method DPD Domestic + COD.').
					'<br />';
				$error_message .= ' '.$this->l('If delivery address is Poland and no COD payment is used please select DPD Domestic shipping method.');
				$this->context->smarty->assign('compatibility_warning_message', $error_message);
			}

			if (!$this->validateAddressForPackageSession((int)$selectedRecipientIdAddress, (int)$id_method))
			{
				$error_message = $this->l('Your develivery address is not compatible with the selected shipping method.').' '.
					$this->l('DPD Poland Domestic is available only if develivery address is Poland.').' '.
					$this->l('DPD Internationl shipping method is available only if delivery address is not Poland.');
				$this->context->smarty->assign('address_warning_message', $error_message);
			}

			$cookie = new Cookie(_DPDPOLAND_COOKIE_);

			$this->context->smarty->assign(array(
				'displayBlock' => true,
				'order' => $order,
				'messages' => $this->html, // Flash messages
				'package' => $package,
				'selected_id_method' => self::getMethodIdByCarrierId($order->id_carrier),
				'settings' => $settings,
				'payerNumbers' => DpdPolandPayerNumber::getPayerNumbers(),
				'selectedPayerNumber' => $selectedPayerNumber,
				'products' => $products,
				'parcels' => $parcels,
				'senderAddress' => $this->getSenderAddress($settings, $package->id_address_sender),
				'recipientAddresses' => $customer->getAddresses($this->context->language->id),
				'selectedRecipientIdAddress' => $selectedRecipientIdAddress,
				'recipientAddress' => $this->getRecipientAddress($selectedRecipientIdAddress),
				'currency_from' => $currency_from,
				'currency_to' => $currency_to,
				'redirect_and_open' => $cookie->dpdpoland_package_id,
				'printout_format' => $cookie->dpdpoland_printout_format
			));

			$this->setGlobalVariablesForAjax();
		}

		//return $this->context->smarty->fetch(_DPDPOLAND_TPL_DIR_.'hook/adminOrder.tpl');
		return $this->context->smarty->fetch(_DPDPOLAND_TPL_DIR_.'hook/adminOrder_16.tpl');
	}

	/* hook is used to filter out non COD payment methods if DPD COD carrier was selected */
	public function hookPaymentTop($params)
	{
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			return $this->disablePaymentMethods();

		if (!Validate::isLoadedObject($this->context->cart) || !$this->context->cart->id_carrier)
			return;

		$method_id = self::getMethodIdByCarrierId((int)$this->context->cart->id_carrier);

		$cache_id = 'exceptionsCache';
		$exceptionsCache = (Cache::isStored($cache_id)) ? Cache::retrieve($cache_id) : array(); // existing cache
		$controller = (Configuration::get('PS_ORDER_PROCESS_TYPE') == 0) ? 'order' : 'orderopc';
		$id_hook = Hook::getIdByName('displayPayment'); // ID of hook we are going to manipulate

		if ($paymentModules = DpdPoland::getPaymentModules())
		{
			foreach ($paymentModules as $module)
			{
				$is_cod_module = Configuration::get(DpdPolandConfiguration::COD_MODULE_PREFIX.$module['name']);

				if ($method_id == _DPDPOLAND_STANDARD_COD_ID_ && !$is_cod_module ||
					$method_id == _DPDPOLAND_STANDARD_ID_ && $is_cod_module ||
					$method_id == _DPDPOLAND_CLASSIC_ID_ && $is_cod_module)
				{
					$module_instance = Module::getInstanceByName($module['name']);

					if (Validate::isLoadedObject($module_instance))
					{
						$key = (int)$id_hook.'-'.(int)$module_instance->id;
						$exceptionsCache[$key][$this->context->shop->id][] = $controller;
					}
				}
			}

			Cache::store($cache_id, $exceptionsCache);
		}
	}

	private function disablePaymentMethods()
	{
		$method_id = self::getMethodIdByCarrierId((int)$this->context->cart->id_carrier);

		if ($paymentModules = DpdPoland::getPaymentModules())
		{
			foreach ($paymentModules as $module)
			{
				$is_cod_module = Configuration::get(DpdPolandConfiguration::COD_MODULE_PREFIX.$module['name']);

				if ($method_id == _DPDPOLAND_STANDARD_COD_ID_ && !$is_cod_module ||
					$method_id == _DPDPOLAND_STANDARD_ID_ && $is_cod_module ||
					$method_id == _DPDPOLAND_CLASSIC_ID_ && $is_cod_module)
				{
					$module_instance = Module::getInstanceByName($module['name']);

					if (Validate::isLoadedObject($module_instance))
						$module_instance->currencies = array();
				}
			}
		}
	}

	public function hookUpdateCarrier($params)
	{
		$id_reference = (int)DpdPolandCarrier::getReferenceByIdCarrier((int)$params['id_carrier']);
		$id_carrier = (int)$params['carrier']->id;

		$dpdpoland_carrier = new DpdPolandCarrier();
		$dpdpoland_carrier->id_carrier = (int)$id_carrier;
		$dpdpoland_carrier->id_reference = (int)$id_reference;
		$dpdpoland_carrier->save();
	}

	public function getOrderShippingCost($cart)
	{
		return $this->getOrderShippingCostExternal($cart);
	}

	public function getOrderShippingCostExternal($cart)
	{
		if (!$this->soapClientExists() || !$this->checkModuleAvailability())
			return false;

		$disabled_countries_ids = DpdPolandCountry::getDisabledCountriesIDs();

		$id_country = (int)Tools::getValue('id_country');

		if (!$id_country)
		{
			$country = Address::getCountryAndState((int)$cart->id_address_delivery);
			$id_country = $country['id_country'];
		}

		if (!$id_method = self::getMethodIdByCarrierId($this->id_carrier))
		{
			self::$carriers[$this->id_carrier] = false;
			return false;
		}

		if (!$id_country || in_array($id_country, $disabled_countries_ids) && $id_method == _DPDPOLAND_CLASSIC_ID_)
			return false;

		if ($id_country)
			$zone = Country::getIdZone($id_country);
		else
			return false;

		if (!$this->id_carrier)
			return false;

		if ($id_country == Country::getByIso(self::POLAND_ISO_CODE) && $id_method == _DPDPOLAND_CLASSIC_ID_ ||
			$id_country != Country::getByIso(self::POLAND_ISO_CODE) && $id_method == _DPDPOLAND_STANDARD_COD_ID_ ||
			$id_country != Country::getByIso(self::POLAND_ISO_CODE) && $id_method == _DPDPOLAND_STANDARD_ID_)
			return false;

		if (isset(self::$carriers[$this->id_carrier]))
			return self::$carriers[$this->id_carrier];

		$total_weight = self::convertWeight($cart->getTotalWeight());

		if (Configuration::get(DpdPolandConfiguration::PRICE_CALCULATION_TYPE) == DpdPolandConfiguration::PRICE_CALCULATION_PRESTASHOP)
		{
			$carrier = new Carrier($this->id_carrier);
			$price = $carrier->getDeliveryPriceByWeight($total_weight, $zone);

			self::$carriers[$this->id_carrier] = $price;
			return self::$carriers[$this->id_carrier];
		}

		$price = DpdPolandCSV::getPrice($total_weight, $id_method, $cart);
		if ($price === false)
			return false;

		$id_currency_pl = Currency::getIdByIsoCode(_DPDPOLAND_CURRENCY_ISO_, (int)$this->context->shop->id);
		$currency_from = new Currency((int)$id_currency_pl);
		$currency_to = $this->context->currency;
		self::$carriers[$this->id_carrier] = Tools::convertPriceFull($price, $currency_from, $currency_to);
		return self::$carriers[$this->id_carrier];
	}
}