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

class DpdPolandManifestListController extends DpdPolandController
{
	const DEFAULT_ORDER_BY = 'date_add';
	const DEFAULT_ORDER_WAY = 'desc';
	const FILENAME = 'manifestList.controller';

	public function printManifest($id_manifest_ws)
	{
		if (is_array($id_manifest_ws))
		{
			if (empty($id_manifest_ws))
				return false;

			if (file_exists(_PS_MODULE_DIR_.'dpdpoland/manifest_duplicated.pdf') &&
				!unlink(_PS_MODULE_DIR_.'dpdpoland/manifest_duplicated.pdf'))
			{
				$error_message = $this->l('Could not delete old PDF file. Please check module permissions');
				$error = $this->module_instance->displayError($error_message);

				return $this->module_instance->outputHTML($error);
			}

			foreach ($id_manifest_ws as $id)
			{
				$manifest = new DpdPolandManifest;
				$manifest->id_manifest_ws = $id;

				if ($pdf_file_contents = $manifest->generate())
				{
					if (file_exists(_PS_MODULE_DIR_.'dpdpoland/manifest_'.(int)$id.'.pdf') &&
						!unlink(_PS_MODULE_DIR_.'dpdpoland/manifest_'.(int)$id.'.pdf'))
					{
						$error_message = $this->l('Could not delete old PDF file. Please check module permissions');
						$error = $this->module_instance->displayError($error_message);
						return $this->module_instance->outputHTML($error);
					}

					$fp = fopen(_PS_MODULE_DIR_.'dpdpoland/manifest_'.(int)$id.'.pdf', 'a');

					if (!$fp)
					{
						$error_message = $this->l('Could not create PDF file. Please check module folder permissions');
						$error = $this->module_instance->displayError($error_message);
						return $this->module_instance->outputHTML($error);
					}

					fwrite($fp, $pdf_file_contents);
					fclose($fp);
				}
				else
				{
					$error_message = $this->module_instance->displayError(reset(DpdPolandManifestWS::$errors));
					return $this->module_instance->outputHTML($error_message);
				}
			}

			include_once(_PS_MODULE_DIR_.'dpdpoland/libraries/PDFMerger/PDFMerger.php');

			$pdf = new PDFMerger;

			foreach ($id_manifest_ws as $id)
			{
				$manifest_pdf_path = _PS_MODULE_DIR_.'dpdpoland/manifest_'.(int)$id.'.pdf';
				$pdf->addPDF($manifest_pdf_path, 'all')->addPDF($manifest_pdf_path, 'all');
			}

			$pdf->merge('file', _PS_MODULE_DIR_.'dpdpoland/manifest_duplicated.pdf');

			ob_end_clean();
			header('Content-type: application/pdf');
			header('Content-Disposition: attachment; filename="manifests_'.time().'.pdf"');
			readfile(_PS_MODULE_DIR_.'dpdpoland/manifest_duplicated.pdf');

			exit;
		}

		$manifest = new DpdPolandManifest;
		$manifest->id_manifest_ws = $id_manifest_ws;

		if ($pdf_file_contents = $manifest->generate())
		{
			if (file_exists(_PS_MODULE_DIR_.'dpdpoland/manifest.pdf') && !unlink(_PS_MODULE_DIR_.'dpdpoland/manifest.pdf'))
			{
				$error_message = $this->l('Could not delete old PDF file. Please check module permissions');
				$error = $this->module_instance->displayError($error_message);
				return $this->module_instance->outputHTML($error);
			}

			if (file_exists(_PS_MODULE_DIR_.'dpdpoland/manifest_duplicated.pdf') && !unlink(_PS_MODULE_DIR_.'dpdpoland/manifest_duplicated.pdf'))
			{
				$error_message = $this->l('Could not delete old PDF file. Please check module permissions');
				$error = $this->module_instance->displayError($error_message);
				return $this->module_instance->outputHTML($error);
			}

			$fp = fopen(_PS_MODULE_DIR_.'dpdpoland/manifest.pdf', 'a');
			if (!$fp)
			{
				$error_message = $this->l('Could not create PDF file. Please check module folder permissions');
				$error = $this->module_instance->displayError($error_message);
				return $this->module_instance->outputHTML($error);
			}

			fwrite($fp, $pdf_file_contents);
			fclose($fp);

			include_once(_PS_MODULE_DIR_.'dpdpoland/libraries/PDFMerger/PDFMerger.php');
			$pdf = new PDFMerger;
			$pdf->addPDF(_PS_MODULE_DIR_.'dpdpoland/manifest.pdf', 'all');
			$pdf->addPDF(_PS_MODULE_DIR_.'dpdpoland/manifest.pdf', 'all');
			$pdf->merge('file', _PS_MODULE_DIR_.'dpdpoland/manifest_duplicated.pdf');

			ob_end_clean();
			header('Content-type: application/pdf');
			header('Content-Disposition: attachment; filename="manifests_'.time().'.pdf"');
			readfile(_PS_MODULE_DIR_.'dpdpoland/manifest_duplicated.pdf');
			exit;
		}
		else
			$this->module_instance->outputHTML($this->module_instance->displayError(reset(DpdPolandManifestWS::$errors)));
	}

	public function getListHTML()
	{
		$keys_array = array('id_manifest_ws', 'count_parcels', 'count_orders', 'date_add');
		$this->prepareListData($keys_array, 'Manifests', new DpdPolandManifest, self::DEFAULT_ORDER_BY, self::DEFAULT_ORDER_WAY, 'manifest_list');

		if (version_compare(_PS_VERSION_, '1.6', '<'))
			return $this->context->smarty->fetch(_DPDPOLAND_TPL_DIR_.'admin/manifest_list.tpl');
		return $this->context->smarty->fetch(_DPDPOLAND_TPL_DIR_.'admin/manifest_list_16.tpl');
	}
}