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

class DpdPolandPackageListController extends DpdPolandController
{
	const DEFAULT_ORDER_BY 	= 'date_add';
	const DEFAULT_ORDER_WAY = 'desc';
	const FILENAME = 'packageList.controller';

	public static function printManifest($module_instance)
	{
		$cookie = Context::getContext()->cookie;

		if (isset($cookie->dpdpoland_packages_ids))
		{
			if (version_compare(_PS_VERSION_, '1.5', '<'))
				$package_ids = unserialize(Context::getContext()->cookie->dpdpoland_packages_ids);
			else
				$package_ids = Tools::unSerialize(Context::getContext()->cookie->dpdpoland_packages_ids);

			unset($cookie->dpdpoland_packages_ids);
			$cookie->write();

			$separated_packages = DpdPolandPackage::separatePackagesBySession($package_ids);
			$international_packages = $separated_packages['INTERNATIONAL'];
			$domestic_packages = $separated_packages['DOMESTIC'];
			$manifest_ids = array();

			if ($international_packages)
				$manifest_ids[] = DpdPolandManifest::getManifestIdByPackageId($international_packages[0]);

			if ($domestic_packages)
				$manifest_ids[] = DpdPolandManifest::getManifestIdByPackageId($domestic_packages[0]);

			require_once(_DPDPOLAND_CONTROLLERS_DIR_.'manifestList.controller.php');

			$manifest_controller = new DpdPolandManifestListController();

			return $manifest_controller->printManifest($manifest_ids);
		}

		if ($package_ids = Tools::getValue('PackagesBox'))
		{
			if (!DpdPolandManifest::validateSenderAddresses($package_ids))
			{
				$error_message = $module_instance->l('Manifests can not have different sender addresses', self::FILENAME);
				$error = $module_instance->displayError($error_message);

				return $module_instance->outputHTML($error);
			}

			$separated_packages = DpdPolandPackage::separatePackagesBySession($package_ids);
			$international_packages = $separated_packages['INTERNATIONAL'];
			$domestic_packages = $separated_packages['DOMESTIC'];

			if ($international_packages)
			{
				$manifest = new DpdPolandManifest;

				if (!$manifest->generateMultiple($international_packages))
				{
					$error = $module_instance->displayError(reset(DpdPolandManifest::$errors));

					return $module_instance->outputHTML($error);
				}
			}

			if ($domestic_packages)
			{
				$manifest = new DpdPolandManifest;

				if (!$manifest->generateMultiple($domestic_packages))
				{
					$error = $module_instance->displayError(reset(DpdPolandManifest::$errors));

					return $module_instance->outputHTML($error);
				}
			}

			$cookie->dpdpoland_packages_ids = serialize($package_ids);
			$redirect_uri = $module_instance->module_url.'&menu=packages_list';

			die(Tools::redirectAdmin($redirect_uri));
		}
	}

	private static function createLabelPDFDocument($package, $module_instance, $packages, $printout_format, $filename)
	{
		if (!$pdf_file_contents = $package->generateLabelsForMultiplePackages($packages, 'PDF', $printout_format))
		{
			$error = $module_instance->displayError(reset(DpdPolandPackage::$errors));

			return $error;
		}

		if (file_exists(_PS_MODULE_DIR_.'dpdpoland/'.$filename) && !unlink(_PS_MODULE_DIR_.'dpdpoland/'.$filename))
		{
			$error_message = $module_instance->l('Could not delete old PDF file. Please check module folder permissions', self::FILENAME);
			$error = $module_instance->displayError($error_message);

			return $error;
		}

		$international_pdf = fopen(_PS_MODULE_DIR_.'dpdpoland/'.$filename, 'w');

		if (!$international_pdf)
		{
			$error_message = $module_instance->l('Could not create PDF file. Please check module folder permissions', self::FILENAME);
			$error = $module_instance->displayError($error_message);

			return $error;
		}

		fwrite($international_pdf, $pdf_file_contents);
		fclose($international_pdf);

		return true;
	}

	public static function printLabels($printout_format)
	{
		$module_instance = Module::getinstanceByName('dpdpoland');

		if ($package_ids = Tools::getValue('PackagesBox'))
		{
			$package = new DpdPolandPackage;

			$separated_packages = DpdPolandPackage::separatePackagesBySession($package_ids);
			$international_packages = $separated_packages['INTERNATIONAL'];
			$domestic_packages = $separated_packages['DOMESTIC'];

			if ($international_packages)
			{
				$result = self::createLabelPDFDocument($package, $module_instance, $international_packages, $printout_format, 'international_labels.pdf');

				if ($result !== true)
					return $module_instance->outputHTML($result);
			}

			if ($domestic_packages)
			{
				$result = self::createLabelPDFDocument($package, $module_instance, $domestic_packages, $printout_format, 'domestic_labels.pdf');

				if ($result !== true)
					return $module_instance->outputHTML($result);
			}

			include_once(_PS_MODULE_DIR_.'dpdpoland/libraries/PDFMerger/PDFMerger.php');

			$pdf = new PDFMerger;

			if ($international_packages && $domestic_packages)
			{
				if (file_exists(_PS_MODULE_DIR_.'dpdpoland/labels_multisession.pdf') && !unlink(_PS_MODULE_DIR_.'dpdpoland/labels_multisession.pdf'))
				{
					$error_message = $module_instance->l('Could not delete old PDF file. Please check module folder permissions', self::FILENAME);
					$error = $module_instance->displayError($error_message);

					return $module_instance->outputHTML($error);
				}

				$international_pdf_path = _PS_MODULE_DIR_.'dpdpoland/international_labels.pdf';
				$domestic_pdf_path = _PS_MODULE_DIR_.'dpdpoland/domestic_labels.pdf';
				$multisession_pdf_path = _PS_MODULE_DIR_.'dpdpoland/labels_multisession.pdf';
				$pdf->addPDF($international_pdf_path, 'all')->addPDF($domestic_pdf_path, 'all')->merge('file', $multisession_pdf_path);
			}

			ob_end_clean();
			header('Content-type: application/pdf');
			header('Content-Disposition: attachment; filename="labels_'.time().'.pdf"');

			if ($international_packages && $domestic_packages)
				readfile(_PS_MODULE_DIR_.'dpdpoland/labels_multisession.pdf');
			elseif ($international_packages)
				readfile(_PS_MODULE_DIR_.'dpdpoland/international_labels.pdf');
			elseif ($domestic_packages)
				readfile(_PS_MODULE_DIR_.'dpdpoland/domestic_labels.pdf');
			else
			{
				$error_message = $module_instance->l('No labels were found', self::FILENAME);
				$error = $module_instance->displayError($error_message);
				return $module_instance->outputHTML($error);
			}
		}
	}

	public function getList()
	{
		$keys_array = array('date_add', 'id_order', 'package_number', 'count_parcel', 'receiver', 'country', 'postcode', 'city', 'address');
		$this->prepareListData($keys_array, 'Packages', new DpdPolandPackage(), self::DEFAULT_ORDER_BY, self::DEFAULT_ORDER_WAY, 'packages_list');
		$this->context->smarty->assign('order_link', 'index.php?controller=AdminOrders&vieworder&token='.Tools::getAdminTokenLite('AdminOrders'));

		if (version_compare(_PS_VERSION_, '1.6', '>='))
			return $this->context->smarty->fetch(_DPDPOLAND_TPL_DIR_.'admin/package_list_16.tpl');

		return $this->context->smarty->fetch(_DPDPOLAND_TPL_DIR_.'admin/package_list.tpl');
	}
}