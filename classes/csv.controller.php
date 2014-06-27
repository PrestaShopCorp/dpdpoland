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

class DpdPolandCSVController extends DpdPolandController
{
	const POLAND_ISO_CODE 				= 'PL';

	private $csv_titles 				= array();
	private $csv_available_formats 		= array();

	const SETTINGS_SAVE_CSV_ACTION 		= 'saveModuleCSVSettings';
	const SETTINGS_DOWNLOAD_CSV_ACTION 	= 'downloadModuleCSVSettings';
	const SETTINGS_DELETE_CSV_ACTION 	= 'deleteModuleCSVSettings';
	const FILENAME 						= 'csv.controller';
	const DEFAULT_FIRST_LINE_INDEX		= 2;

	const STAR_COUNTRY_ERROR			= 1;
	const PRESTASHOP_COUNTRY_ERROR		= 2;
	const PL_COUNTRY_ERROR				= 3;
	const NOT_PL_COUNTRY_ERROR			= 4;

	public function __construct()
	{
		parent::__construct();

		$this->csv_titles = array(
			'iso_country' 		=> $this->l('Country'),
			'weight_from' 		=> $this->l('Parcel weight from (kg)'),
			'weight_to' 		=> $this->l('Parcel weight to (kg)'),
			'parcel_price' 		=> $this->l('Parcel price (PLN)'),
			'id_carrier' 		=> $this->l('Carrier'),
			'cod_price' 		=> $this->l('COD cost (PLN)')
		);

		$this->csv_available_formats = array(
			'text/csv',
			'text/plain',
			'application/csv',
			'text/comma-separated-values',
			'application/excel',
			'application/vnd.ms-excel',
			'application/vnd.msexcel',
			'text/anytext',
			'application/octet-stream',
			'application/txt',
			'application/force-download'
		);
	}

	public function getCSVPage()
	{
		$selected_pagination = Tools::getValue('pagination', '20');
		$page = Tools::getValue('current_page', '1');
		$start = ($selected_pagination * $page) - $selected_pagination;
		$limit = ' LIMIT '.$start.', '.$selected_pagination.' ';

		$selected_products_data = DpdPolandCSV::getAllData($limit);
		$list_total = count(DpdPolandCSV::getAllData());
		$pagination = array(20, 50, 100, 300);

		$total_pages = ceil($list_total / $selected_pagination);

		if (!$total_pages)
			$total_pages = 1;

		$this->context->smarty->assign(array(
			'saveAction' => $this->module_instance->module_url.'&menu=csv',
			'csv_data' => $selected_products_data,
			'page' => $page,
			'total_pages' => $total_pages,
			'pagination' => $pagination,
			'list_total' => $list_total,
			'selected_pagination' => $selected_pagination
		));

		return $this->context->smarty->fetch(_DPDPOLAND_TPL_DIR_.'admin/csv.tpl');
	}

	public static function init()
	{
		$controller = new DpdPolandCSVController;

		if (Tools::isSubmit(DpdPolandCSVController::SETTINGS_SAVE_CSV_ACTION))
		{
			$csv_data = $controller->readCSVData();
			if ($csv_data === false)
			{
				DpdPoland::addFlashError($controller->l('Wrong CSV file'));
				Tools::redirectAdmin($controller->module_instance->module_url.'&menu=csv');
			}

			$message = $controller->validateCSVData($csv_data);
			if ($message !== true)
				return $controller->module_instance->outputHTML($controller->module_instance->displayErrors($message));

			if ($controller->saveCSVData($csv_data))
				DpdPoland::addFlashMessage($controller->l('CSV data was successfully saved'));
			else
				DpdPoland::addFlashError($controller->l('CSV data could not be saved'));

			Tools::redirectAdmin($controller->module_instance->module_url.'&menu=csv');
		}

		if (Tools::isSubmit(DpdPolandCSVController::SETTINGS_DELETE_CSV_ACTION))
			$controller->deleteCSV();
	}

	private function deleteCSV()
	{
		if (DpdPolandCSV::deleteAllData())
			DpdPoland::addFlashMessage($this->l('Price rules deleted successfully'));
		else
			DpdPoland::addFlashError($this->l('Price rules could not be deleted'));

		Tools::redirectAdmin($this->module_instance->module_url.'&menu=csv');
	}

	public function generateCSV()
	{
		$csv_data = array($this->csv_titles);
		$csv_data = array_merge($csv_data, DpdPolandCSV::getCSVData());
		$this->arrayToCSV($csv_data, _DPDPOLAND_CSV_FILENAME_.'.csv', _DPDPOLAND_CSV_DELIMITER_);
	}

	private function arrayToCSV($array, $filename, $delimiter)
	{
		// open raw memory as file so no temp files needed, you might run out of memory though
		$f = fopen('php://memory', 'w');
		// loop over the input array
		foreach ($array as $line)
		{
			// generate csv lines from the inner arrays
			fputcsv($f, $line, $delimiter);
		}
		// rewrind the "file" with the csv lines
		fseek($f, 0);
		// tell the browser it's going to be a csv file
		header('Content-Type: application/csv; charset=utf-8');
		// tell the browser we want to save it instead of displaying it
		header('Content-Disposition: attachement; filename="'.$filename.'"');
		// make php send the generated csv lines to the browser
		fpassthru($f);
		exit;
	}

	private function validateCSVData($csv_data)
	{
		$errors = array();

		if (!$this->validateCSVStructure($csv_data))
		{
			$errors[] = $this->l('Wrong CSV file structure or empty lines');
			return $errors;
		}

		if (!$this->validatePLNCurrency($csv_data))
		{
			$errors[] = $this->l('PLN currency must be installed before CSV import.');
			return $errors;
		}

		$countries_validation = $this->validateCSVCountries($csv_data);
		if ($countries_validation !== true)
		{
			if (!empty($countries_validation[0]))
				$errors[] = $this->l('Country: country code does not exist in your PrestaShop system. Invalid lines:').' '.$countries_validation[0];
			if (!empty($countries_validation[1]))
				$errors[] = sprintf($this->l('Country: country "*" can be used only with carrier ID %d. Invalid lines:'), _DPDPOLAND_CLASSIC_ID_).
					' '.$countries_validation[1];
			if (!empty($countries_validation[2]))
				$errors[] = sprintf($this->l('Country: country code PL can be used only with carrier ID %1$d or %2$d. Invalid lines:'),
					_DPDPOLAND_STANDARD_ID_, _DPDPOLAND_STANDARD_COD_ID_).' '.$countries_validation[2];
			if (!empty($countries_validation[3]))
				$errors[] = sprintf($this->l('Country: international shipping is compatible only with carrier ID %d. Invalid lines:'),
					_DPDPOLAND_CLASSIC_ID_).' '.$countries_validation[3];
		}

		$weight_from_validation = $this->validateCSVWeightFrom($csv_data);

		if ($weight_from_validation !== true)
			$errors[] = $this->l('Weight (From): invalid lines:').' '.$weight_from_validation;

		$weight_to_validation = $this->validateCSVWeightTo($csv_data);

		if ($weight_to_validation !== true)
			$errors[] = $this->l('Weight (To): invalid lines:').' '.$weight_to_validation;

		$parcel_prices_validation = $this->validateCSVParcelPrices($csv_data);

		if ($parcel_prices_validation !== true)
			$errors[] = $this->l('Parcel price (PLN): invalid lines:').' '.$parcel_prices_validation;

		$carrier_validation = $this->validateCSVCarriers($csv_data);

		if ($carrier_validation !== true)
			$errors[] = $this->l('Carrier: invalid lines:').' '.$carrier_validation;

		$cod_price = $this->validateCSVCODPrice($csv_data);

		if ($cod_price !== true)
		{
			if (!empty($cod_price[0]))
				$errors[] = $this->l('COD cost (PLN): Value should be >=0. Invalid lines:').' '.$cod_price[0];
			if (!empty($cod_price[1]))
				$errors[] = $this->l('COD cost (PLN): It\'s possible to use COD only with "DPD domestic shipment - Standard with COD". Leave empty COD field otherwise. Invalid lines:').' '.$cod_price[1];
			if (!empty($cod_price[2]))
$errors[] = $this->l('COD cost (PLN): COD is available only in Poland. Leave empty COD field otherwise. Invalid lines:').' '.$cod_price[2];
		}

		if (!empty($errors))
			return $errors;

		return true;
	}

	private function validatePLNCurrency()
	{
		return (bool)Currency::getIdByIsoCode('PLN');
	}

	private function validateCSVStructure($csv_data)
	{
		$csv_data_count = count($csv_data);
		for ($i = 0; $i < $csv_data_count; $i++)
			if (!isset($csv_data[$i][DpdPolandCSV::COLUMN_COD_PRICE]))
				return false;

		return true;
	}

	private function validateCSVCountries($csv_data)
	{
		$csv_data_count = count($csv_data); //number of CSV data lines without titles
		$wrong_countries = '';
		$wrong_star_countries = '';
		$wrong_pl_countries = '';
		$wrong_not_pl_countries = '';

		for ($i = 0; $i < $csv_data_count; $i++)
		{
			$country_validation = $this->validateCSVCountry($csv_data[$i][DpdPolandCSV::COLUMN_COUNTRY], $csv_data[$i][DpdPolandCSV::COLUMN_CARRIER]);

			if ($country_validation !== true)
			{
				switch ($country_validation)
				{
					case self::STAR_COUNTRY_ERROR:
						$wrong_star_countries .= ($i + self::DEFAULT_FIRST_LINE_INDEX).', ';
						break;
					case self::PRESTASHOP_COUNTRY_ERROR:
						$wrong_countries .= ($i + self::DEFAULT_FIRST_LINE_INDEX).', ';
						break;
					case self::PL_COUNTRY_ERROR:
						$wrong_pl_countries .= ($i + self::DEFAULT_FIRST_LINE_INDEX).', ';
						break;
					case self::NOT_PL_COUNTRY_ERROR:
						$wrong_not_pl_countries .= ($i + self::DEFAULT_FIRST_LINE_INDEX).', ';
						break;
					default:
						break;
				}
			}
		}

		if (!empty($wrong_countries))
			$wrong_countries = Tools::substr($wrong_countries, 0, -2); //remove last two symbols (comma & space)

		if (!empty($wrong_star_countries))
			$wrong_star_countries = Tools::substr($wrong_star_countries, 0, -2); //remove last two symbols (comma & space)

		if (!empty($wrong_pl_countries))
			$wrong_pl_countries = Tools::substr($wrong_pl_countries, 0, -2); //remove last two symbols (comma & space)

		if (!empty($wrong_not_pl_countries))
			$wrong_not_pl_countries = Tools::substr($wrong_not_pl_countries, 0, -2); //remove last two symbols (comma & space)

		return empty($wrong_countries) && empty($wrong_star_countries) && empty($wrong_pl_countries) && empty($wrong_not_pl_countries) ? true :
			array($wrong_countries, $wrong_star_countries, $wrong_pl_countries, $wrong_not_pl_countries);
	}

	private function validateCSVCountry($iso_code, $id_carrier)
	{
		if ($iso_code === '*')
			if ($id_carrier == _DPDPOLAND_CLASSIC_ID_)
				return true;
			else
				return self::STAR_COUNTRY_ERROR;

		if (!Country::getByIso($iso_code))
			return self::PRESTASHOP_COUNTRY_ERROR;

		if ($iso_code == self::POLAND_ISO_CODE)
			if ($id_carrier == _DPDPOLAND_STANDARD_ID_ || $id_carrier == _DPDPOLAND_STANDARD_COD_ID_)
				return true;
			else
				return self::PL_COUNTRY_ERROR;
		else
			if ($id_carrier == _DPDPOLAND_CLASSIC_ID_)
				return true;
			else
				return self::NOT_PL_COUNTRY_ERROR;
	}

	private function validateCSVWeightFrom($csv_data)
	{
		$wrong_weights = '';
		$csv_data_count = count($csv_data);

		for ($i = 0; $i < $csv_data_count; $i++)
			if (!Validate::isFloat($csv_data[$i][DpdPolandCSV::COLUMN_WEIGHT_FROM]) ||
				$csv_data[$i][DpdPolandCSV::COLUMN_WEIGHT_FROM] < 0)
				$wrong_weights .= ($i + self::DEFAULT_FIRST_LINE_INDEX).', ';

		if (!empty($wrong_weights))
			$wrong_weights = Tools::substr($wrong_weights, 0, -2); //remove last two symbols (comma & space)

		return empty($wrong_weights) ? true : $wrong_weights;
	}

	private function validateCSVWeightTo($csv_data)
	{
		$wrong_weights = '';
		$csv_data_count = count($csv_data);
		for ($i = 0; $i < $csv_data_count; $i++)
			if (!Validate::isFloat($csv_data[$i][DpdPolandCSV::COLUMN_WEIGHT_TO]) ||
				$csv_data[$i][DpdPolandCSV::COLUMN_WEIGHT_TO] <= 0 ||
				$csv_data[$i][DpdPolandCSV::COLUMN_WEIGHT_TO] < $csv_data[$i][DpdPolandCSV::COLUMN_WEIGHT_FROM])
				$wrong_weights .= ($i + self::DEFAULT_FIRST_LINE_INDEX).', ';

		if (!empty($wrong_weights))
			$wrong_weights = Tools::substr($wrong_weights, 0, -2); //remove last two symbols (comma & space)

		return empty($wrong_weights) ? true : $wrong_weights;
	}

	private function validateCSVParcelPrices($csv_data)
	{
		$wrong_prices = '';
		$csv_data_count = count($csv_data);
		for ($i = 0; $i < $csv_data_count; $i++)
			if (!Validate::isFloat($csv_data[$i][DpdPolandCSV::COLUMN_PARCEL_PRICE]) ||
				$csv_data[$i][DpdPolandCSV::COLUMN_PARCEL_PRICE] < 0)
				$wrong_prices .= ($i + self::DEFAULT_FIRST_LINE_INDEX).', ';

		if (!empty($wrong_prices))
			$wrong_prices = Tools::substr($wrong_prices, 0, -2); //remove last two symbols (comma & space)

		return empty($wrong_prices) ? true : $wrong_prices;
	}

	private function validateCSVCarriers($csv_data)
	{
		$available_methods = array(_DPDPOLAND_STANDARD_ID_, _DPDPOLAND_STANDARD_COD_ID_, _DPDPOLAND_CLASSIC_ID_);
		$wrong_methods = '';
		$csv_data_count = count($csv_data);

		for ($i = 0; $i < $csv_data_count; $i++)
			if (!in_array($csv_data[$i][DpdPolandCSV::COLUMN_CARRIER], $available_methods) && $csv_data[$i][DpdPolandCSV::COLUMN_CARRIER] !== '*')
				$wrong_methods .= ($i + self::DEFAULT_FIRST_LINE_INDEX).', ';

		if (!empty($wrong_methods))
			$wrong_methods = Tools::substr($wrong_methods, 0, -2); //remove last two symbols (comma & space)

		return empty($wrong_methods) ? true : $wrong_methods;
	}

	private function validateCSVCODPrice($csv_data)
	{
		$wrong_price = '';
		$wrong_price_method = '';
		$wrong_price_country = '';
		$csv_data_count = count($csv_data);

		for ($i = 0; $i < $csv_data_count; $i++)
		{
			if (!empty($csv_data[$i][DpdPolandCSV::COLUMN_COD_PRICE]))
			{
				if (!Validate::isFloat($csv_data[$i][DpdPolandCSV::COLUMN_COD_PRICE]) ||
					$csv_data[$i][DpdPolandCSV::COLUMN_COD_PRICE] < 0)
					$wrong_price .= ($i + self::DEFAULT_FIRST_LINE_INDEX).', ';
				else
				{
					if ($csv_data[$i][DpdPolandCSV::COLUMN_CARRIER] != _DPDPOLAND_STANDARD_COD_ID_)
						$wrong_price_method .= ($i + self::DEFAULT_FIRST_LINE_INDEX).', ';

					if ($csv_data[$i][DpdPolandCSV::COLUMN_COUNTRY] !== self::POLAND_ISO_CODE)
						$wrong_price_country .= ($i + self::DEFAULT_FIRST_LINE_INDEX).', ';
				}
			}
		}

		if (!empty($wrong_price))
			$wrong_price = Tools::substr($wrong_price, 0, -2); //remove last two symbols (comma & space)

		if (!empty($wrong_price_method))
			$wrong_price_method = Tools::substr($wrong_price_method, 0, -2); //remove last two symbols (comma & space)

		if (!empty($wrong_price_country))
			$wrong_price_country = Tools::substr($wrong_price_country, 0, -2); //remove last two symbols (comma & space)

		return empty($wrong_price) && empty($wrong_price_method) && empty($wrong_price_country) ? true :
			array($wrong_price, $wrong_price_method, $wrong_price_country);
	}

	private function readCSVData()
	{
		if ($_FILES[DpdPolandCSV::CSV_FILE]['error'])
			return false;

		if (!in_array($_FILES[DpdPolandCSV::CSV_FILE]['type'], $this->csv_available_formats))
			return false;

		$csv_data = array();
		$row = 0;

		if (($handle = fopen($_FILES[DpdPolandCSV::CSV_FILE]['tmp_name'], 'r')) !== false)
		{
			while (($data = fgetcsv($handle, 1000, _DPDPOLAND_CSV_DELIMITER_)) !== false)
			{
				if (!$data) continue;
				$csv_data_line = array();
				$row++;
				if ($row == 1)
					continue;
				$num = count($data);
				$row++;
				for ($i = 0; $i < $num; $i++)
					$csv_data_line[] = $data[$i];
				$csv_data[] = $csv_data_line;
			}
			fclose($handle);
		}

		return $csv_data;
	}

	private function saveCSVData($csv_data)
	{
		if (!DpdPolandCSV::deleteAllData())
			return false;

		$success = true;

		foreach ($csv_data as $data)
		{
			$csv_obj = new DpdPolandCSV();
			$csv_obj->id_shop = (int)$this->context->shop->id;
			$csv_obj->iso_country = $data[DpdPolandCSV::COLUMN_COUNTRY];
			$csv_obj->weight_from = $data[DpdPolandCSV::COLUMN_WEIGHT_FROM];
			$csv_obj->weight_to = $data[DpdPolandCSV::COLUMN_WEIGHT_TO];
			$csv_obj->parcel_price = $data[DpdPolandCSV::COLUMN_PARCEL_PRICE];
			$csv_obj->id_carrier = $data[DpdPolandCSV::COLUMN_CARRIER];
			$csv_obj->cod_price = $data[DpdPolandCSV::COLUMN_COD_PRICE];
			$success &= $csv_obj->save();
		}

		return $success;
	}
}