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

class DpdPolandManifestWS extends DpdPolandWS
{
	public function generate(DpdPolandManifest $manifest, $output_doc_format, $output_doc_page_format, $policy)
	{
		$package = $manifest->getPackageInstance();
		$packages = $manifest->getPackages();
		$package_number = null;

		foreach ($packages as $id_package_ws)
		{
			$current_package = new DpdPolandPackage((int)$id_package_ws);

			if ($package_number === null)
				$package_number = $current_package->payerNumber;
			elseif ($package_number !== $current_package->payerNumber)
				$package_number = 'null';
		}

		$params = array(
			'dpdServicesParamsV1' => array(
				'pickupAddress' => $package->getSenderAddress($package_number),
				'policy' => $policy,
				'session' => array(
					'sessionId' => (int)$package->sessionId,
					'sessionType' => $package->getSessionType()
				)
			),
			'outputDocFormatV1' => $output_doc_format,
			'outputDocPageFormatV1' => $output_doc_page_format
		);

		if ($manifest->id_manifest_ws)
			$params['dpdServicesParamsV1']['documentId'] = (int)$manifest->id_manifest_ws;

		$result = $this->generateProtocolV1($params);

		if (isset($result['documentData']) ||
			(isset($result['session']) && isset($result['session']['statusInfo']) && $result['session']['statusInfo']['status'] == 'OK'))
		{
			if (!$manifest->id_manifest_ws)
				$manifest->id_manifest_ws = (int)$result['documentId'];

			if (!$manifest->getPackageIdWsByManifestIdWs($manifest->id_manifest_ws) && !$manifest->save())
				return false;

			return $result['documentData'];
		}
		else
			return false;
	}

	public function generateMultiple($package_ids, $output_doc_format = 'PDF', $output_doc_page_format = 'LBL_PRINTER', $policy = 'STOP_ON_FIRST_ERROR')
	{
		$session_type = '';
		$package_number = null;

		foreach ($package_ids as $id_package_ws)
		{
			$package = new DpdPolandPackage((int)$id_package_ws);

			if (!$session_type || $session_type == $package->getSessionType())
			{
				$session_type = $package->getSessionType();

				if ($package_number === null)
					$package_number = $package->payerNumber;
				elseif ($package_number !== $package->payerNumber)
					$package_number = 'null';
			}
			else
			{
				self::$errors[] = $this->l('Manifests of DOMESTIC shipments cannot be mixed with INTERNATIONAL shipments');
				return false;
			}
		}

		$params = array(
			'dpdServicesParamsV1' => array(
				'pickupAddress' => $package->getSenderAddress($package_number),
				'policy' => $policy,
				'session' => array(
					'sessionType' => $session_type,
					'packages' => array()
				)
			),
			'outputDocFormatV1' => $output_doc_format,
			'outputDocPageFormatV1' => $output_doc_page_format
		);

		foreach ($package_ids as $id_package_ws)
		{
			$params['dpdServicesParamsV1']['session']['packages'][] = array(
				'packageId' => (int)$id_package_ws
			);
		}

		$result = $this->generateProtocolV1($params);

		if (isset($result['session']) && isset($result['session']['statusInfo']) && $result['session']['statusInfo']['status'] == 'OK')
		{
			foreach ($package_ids as $id_package_ws)
			{
				$manifest = new DpdPolandManifest;
				$manifest->id_manifest_ws = (int)$result['documentId'];
				$manifest->id_package_ws = (int)$id_package_ws;

				if (!$manifest->save())
					return false;
			}

			return $result['documentData'];
		}
		else
		{
			if (isset($result['session']['statusInfo']['description']))
				self::$errors[] = $result['session']['statusInfo']['description'];
			elseif (isset($result['session']['statusInfo']['status']))
				self::$errors[] = $result['session']['statusInfo']['status'];

			return false;
		}
	}
}