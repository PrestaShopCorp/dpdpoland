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

include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../init.php');

$module_instance = Module::getInstanceByName('dpdpoland');

if (!Tools::isSubmit('token') || (Tools::isSubmit('token')) && Tools::getValue('token') != sha1(_COOKIE_KEY_.$module_instance->name)) exit;

if (Tools::isSubmit('printLabels'))
{
	$cookie = new Cookie(_DPDPOLAND_COOKIE_);
	if (isset($cookie->dpdpoland_package_id))
	{
		$package_id = $cookie->dpdpoland_package_id;
		$printout_format = $cookie->dpdpoland_printout_format;
		unset($cookie->dpdpoland_package_id);
		unset($cookie->dpdpoland_printout_format);
		$cookie->write();
		$package = new DpdPolandPackage((int)$package_id);
		$pdf_file_contents = $package->generateLabels('PDF', $printout_format);

		ob_end_clean();
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="package_labels_'.(int)Tools::getValue('id_package').'.pdf"');
		echo $pdf_file_contents;
		exit;
	}

	$package = new DpdPolandPackage((int)Tools::getValue('id_package'));

	if ($pdf_file_contents = $package->generateLabels())
	{
		$cookie->dpdpoland_package_id = (int)Tools::getValue('id_package');

		$printout_format = Tools::getValue('printout_format');
		if (!in_array($printout_format, array(DpdPolandConfiguration::PRINTOUT_FORMAT_A4, DpdPolandConfiguration::PRINTOUT_FORMAT_LABEL)))
			$printout_format = DpdPolandConfiguration::PRINTOUT_FORMAT_A4;

		$cookie->dpdpoland_printout_format = $printout_format;
		Tools::redirectAdmin(Tools::getValue('returnOnErrorTo').'&scrollToShipment');
		exit;
	}
	else
	{
		DpdPoland::addFlashError(reset(DpdPolandPackage::$errors));
		Tools::redirectAdmin(Tools::getValue('returnOnErrorTo').'&scrollToShipment');
		exit;
	}
}

if (Tools::isSubmit('downloadModuleCSVSettings'))
{
	include_once(dirname(__FILE__).'/classes/csv.controller.php');
	$controller = new DpdPolandCSVController;
	$controller->generateCSV();
}