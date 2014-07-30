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

class DpdPolandCountryListController extends DpdPolandController
{
	const DEFAULT_ORDER_BY = 'id_country';
	const DEFAULT_ORDER_WAY = 'asc';
	const FILENAME = 'countryList.controller';

	public function __construct()
	{
		parent::__construct();
		$this->init();
	}

	private function init()
	{
		if (Tools::getValue('disable_country') && $id_country = Tools::getValue('id_country'))
			if ($this->changeEnabled((int)$id_country, true))
				$this->displaySuccessStatusChangingMessage();
			else
				$this->module_instance->displayError($this->l('Could not change country status'));

		if (Tools::getValue('enable_country') && $id_country = Tools::getValue('id_country'))
			if ($this->changeEnabled((int)$id_country))
				$this->displaySuccessStatusChangingMessage();
			else
				$this->module_instance->displayError($this->l('Could not change country status'));

		if (Tools::isSubmit('disableCountries'))
		{
			if ($countries = Tools::getValue('CountriesBox'))
				$this->changeEnabledMultipleCountries($countries, true);
			else
			{
				$this->module_instance->outputHTML(
					$this->module_instance->displayError($this->l('No selected countries'))
				);
			}
		}

		if (Tools::isSubmit('enableCountries'))
		{
			if ($countries = Tools::getValue('CountriesBox'))
				$this->changeEnabledMultipleCountries($countries);
			else
			{
				$this->module_instance->outputHTML(
					$this->module_instance->displayError($this->l('No selected countries'))
				);
			}
		}
	}

	private function displaySuccessStatusChangingMessage()
	{
		$page = (int)Tools::getValue('submitFilterCountries');

		if (!$page)
			$page = 1;

		$selected_pagination = (int)Tools::getValue('pagination', $this->pagination[0]);
		$order_by = Tools::getValue('CountryOrderBy', self::DEFAULT_ORDER_BY);
		$order_way = Tools::getValue('CountryOrderWay', self::DEFAULT_ORDER_WAY);

		DpdPoland::addFlashMessage($this->l('Country status changed successfully'));

		$redirect_url = $this->module_instance->module_url;
		$redirect_url .= '&menu=country_list&pagination='.$selected_pagination;
		$redirect_url .= '&CountryOrderBy='.$order_by;
		$redirect_url .= '&CountryOrderWay='.$order_way;
		$redirect_url .= '&submitFilterCountries='.$page;

		die(Tools::redirectAdmin($redirect_url));
	}

	private function changeEnabledMultipleCountries($countries = array(), $disable = false)
	{
		foreach ($countries as $id_country)
			if (!$this->changeEnabled((int)$id_country, $disable))
				self::$errors[] = sprintf($this->l('Could not change country status, ID: %s'), $id_country);

		if (!empty(self::$errors))
		{
			$this->module_instance->outputHTML(
				$this->module_instance->displayErrors(
					self::$errors
				)
			);

			reset(self::$errors);
		}
		else
		{
			$page = (int)Tools::getValue('submitFilterCountries');

			if (!$page)
				$page = 1;

			$selected_pagination = (int)Tools::getValue('pagination', $this->pagination[0]);
			$order_by = Tools::getValue('CountryOrderBy', self::DEFAULT_ORDER_BY);
			$order_way = Tools::getValue('CountryOrderWay', self::DEFAULT_ORDER_WAY);

			DpdPoland::addFlashMessage($this->l('Selected countries statuses changed successfully'));

			$redirect_url = $this->module_instance->module_url;
			$redirect_url .= '&menu=country_list&pagination='.$selected_pagination;
			$redirect_url .= '&CountryOrderBy='.$order_by;
			$redirect_url .= '&CountryOrderWay='.$order_way;
			$redirect_url .= '&submitFilterCountries='.$page;

			die(Tools::redirectAdmin($redirect_url));
		}
	}

	private function changeEnabled($id_country, $disable = false)
	{
		$country_obj = new DpdPolandCountry(DpdPolandCountry::getIdByCountry((int)$id_country));
		$country_obj->enabled = $disable ? 0 : 1;
		$country_obj->id_country = (int)$id_country;
		return $country_obj->save();
	}

	public function getListHTML()
	{
		$keys_array = array('id_country', 'name', 'iso_code', 'enabled');
		$this->prepareListData($keys_array, 'Countries', new DpdPolandCountry(), self::DEFAULT_ORDER_BY, self::DEFAULT_ORDER_WAY, 'country_list');

		if (version_compare(_PS_VERSION_, '1.6', '>='))
			return $this->context->smarty->fetch(_DPDPOLAND_TPL_DIR_.'admin/country_list_16.tpl');

		return $this->context->smarty->fetch(_DPDPOLAND_TPL_DIR_.'admin/country_list.tpl');
	}

	public static function disableDefaultCountries()
	{
		$context = Context::getContext();
		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			foreach (Country::getCountries((int)$context->language->id) as $country)
				if (!in_array($country['iso_code'], DpdPolandCountry::$default_enabled_countries) &&
					!DpdPolandCountry::addCountry($country['id_country'], (int)$context->shop->id, 0))
					return false;
		}
		else
			foreach (array_keys(Shop::getShops()) as $id_shop)
				foreach (Country::getCountriesByIdShop($id_shop, Configuration::get('PS_LANG_DEFAULT')) as $country)
					if (!in_array($country['iso_code'], DpdPolandCountry::$default_enabled_countries) &&
						!DpdPolandCountry::addCountry($country['id_country'], (int)$id_shop, 0))
						return false;

		return true;
	}
}