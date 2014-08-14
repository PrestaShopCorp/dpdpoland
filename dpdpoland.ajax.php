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

if (Tools::isSubmit('getFormatedAddressHTML'))
{
	$id_address = (int)Tools::getValue('id_address');
	echo $module_instance->getFormatedAddressHTML($id_address);
	exit;
}

if (Tools::isSubmit('getProducts'))
{
	echo Tools::jsonEncode($module_instance->searchProducts(Tools::getValue('q')));
	exit;
}

if (Tools::isSubmit('savePackagePrintLabels'))
{
	if (!$id_package = $module_instance->savePackageFromPost())
	{
		die(Tools::jsonEncode(array(
			'error' => reset(DpdPoland::$errors)
		)));
	}

	$printout_format = Tools::getValue('dpdpoland_printout_format');

	if ($printout_format != DpdPolandConfiguration::PRINTOUT_FORMAT_LABEL && $printout_format != DpdPolandConfiguration::PRINTOUT_FORMAT_A4)
		$printout_format = DpdPolandConfiguration::PRINTOUT_FORMAT_LABEL;

	die(Tools::jsonEncode(array(
		'error' => false,
		'id_package' => (int)$id_package,
		'link_to_labels_pdf' => '?printLabels&id_package='.(int)$id_package.'&printout_format='.$printout_format.'&token='.Tools::getValue('token')
	)));
}

if (Tools::isSubmit('printLabels'))
{
	$printout_format = Tools::getValue('dpdpoland_printout_format');

	if ($printout_format != DpdPolandConfiguration::PRINTOUT_FORMAT_LABEL && $printout_format != DpdPolandConfiguration::PRINTOUT_FORMAT_A4)
		$printout_format = DpdPolandConfiguration::PRINTOUT_FORMAT_LABEL;

	die(Tools::jsonEncode(array(
		'error' => false,
		'link_to_labels_pdf' => '?printLabels&id_package='.(int)Tools::getValue('id_package').
		'&printout_format='.$printout_format.'&token='.Tools::getValue('token')
	)));
}

if (Tools::getValue('addDPDClientNumber'))
{
	$result = $module_instance->addDPDClientNumber();
	die(Tools::jsonEncode($result));
}

if (Tools::getValue('deleteDPDClientNumber'))
{
	$result = $module_instance->deleteDPDClientNumber();
	die(Tools::jsonEncode($result));
}

if (Tools::getValue('getPayerNumbersTableHTML'))
{
	$html = $module_instance->getPayerNumbersTableHTML();
	die(Tools::jsonEncode($html));
}

if (Tools::getValue('calculateTimeLeft'))
{
	$time_left = $module_instance->calculateTimeLeft();
	die(Tools::jsonEncode($time_left));
}

if (Tools::getValue('getTimeFrames'))
{
	$html = $module_instance->getTimeFrames();
	die(Tools::jsonEncode($html));
}