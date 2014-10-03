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

class DpdPolandPackageWS extends DpdPolandWS
{
	const FILENAME = 'Package';

	private $parcels = array();

	private $services = array();

	private $sender = array();

	private $receiver = array();

	private $data = array();

	public function addParcel($parcel, $additional_info)
	{
		$parcel = array(
			'content' => $parcel['content'],
			'customerData1' => $additional_info,
			'customerData2' => null,
			'customerData3' => null,
			'reference' => Tools::strtoupper(Tools::passwdGen(9, 'NO_NUMERIC')).'_'.(int)$parcel['number'],
			'sizeX' => (float)$parcel['length'],
			'sizeY' => (float)$parcel['width'],
			'sizeZ' => (float)$parcel['height'],
			'weight' => (float)$parcel['weight']
		);

		$this->parcels[] = $parcel;
	}

	private function getErrorsByKey($response, $error_key, $errors = array())
	{
		if (!empty($response))
			foreach ($response as $key => $value)
				if (is_object($value) || is_array($value))
					$errors = $this->getErrorsByKey($value, $error_key, $errors);
				elseif ($key == $error_key)
					$errors[] = $value;
		
		return $errors;
	}

	public function create(DpdPolandPackage $package_obj)
	{
		if ($result = $this->createRemotely($package_obj))
		{
			if (isset($result['Status']) && $result['Status'] == 'OK')
			{
				$package = $result['Packages']['Package'];
				$package_obj->id_package_ws = (int)$package['PackageId'];
				$package_obj->sessionId = (int)$result['SessionId'];

				if (!$package_obj->save())
					self::$errors[] = $this->l('Package was successfully created but we were unable to save its data locally');

				return $package;
			}
			else
			{
				if (isset($result['Packages']['InvalidFields']))
					$errors = $result['Packages']['InvalidFields'];
				elseif (isset($result['faultcode']) && isset($result['faultstring']))
					$errors = $result['faultcode'].' : '.$result['faultstring'];
				else
				{
					$errors = array();

					if ($error_ids = $this->getErrorsByKey($result, 'ErrorId'))
					{
						$language = new DpdPolandLanguage();
						
						foreach ($error_ids as $id_error)
							$errors[] = $language->getTranslation($id_error);
					}
					elseif ($error_messages = $this->getErrorsByKey($result, 'Info'))
					{
						foreach ($error_messages as $message)
							$errors[] = $message;
					}

					$errors = reset($errors);

					if (!$errors)
						$errors = $this->module_instance->displayName.' : '.$this->l('Unknown error');
				}

				if (is_array($errors))
				{
					$errors = (array_values($errors) === $errors) ? $errors : array($errors); // array must be multidimentional

					foreach ($errors as $error)
						self::$errors[] = $error['info'];
				}
				else
					self::$errors[] = $errors;

				return false;
			}
		}

		return false;
	}

	/**
	 * Creates packages
	 * $payerType (SENDER, THIRD_PARTY)
	 */

	private function createRemotely(DpdPolandPackage $package_obj, $payerType = 'SENDER')
	{
		if (!$this->prepareReceiverAddress($package_obj))
			return false;

		$payer_number = Tools::getValue('dpdpoland_PayerNumber');

		$this->prepareSenderAddress($payer_number);
		$this->prepareServicesData($package_obj);

		$params = array(
			'openUMLV1' => array(
				'packages' => array(
					'parcels' => $this->parcels,
					'payerType' => $payerType,
					'receiver' => $this->receiver,
					'ref1' => $package_obj->ref1,
					'ref2' => $package_obj->ref2,
					'ref3' => _DPDPOLAND_REFERENCE3_,
					'reference' => null,
					'sender' => $this->sender,
					'services' => $this->services,
					'thirdPartyFID' => null
				)
			),
			'pkgNumsGenerationPolicyV1' => 'STOP_ON_FIRST_ERROR',
			'langCode' => 'PL'
		);

		return $this->generatePackagesNumbersV2($params);
	}

	private function prepareReceiverAddress(DpdPolandPackage $package_obj)
	{
		$address = new Address((int)$package_obj->id_address_delivery);

		if (Validate::isLoadedObject($address))
		{
			$customer = new Customer((int)$address->id_customer);

			if (Validate::isLoadedObject($customer))
			{
				$this->receiver = array(
					'address' => $address->address1,
					'city' => $address->city,
					'company' => $address->company,
					'countryCode' => Country::getIsoById((int)$address->id_country),
					'email' => $customer->email,
					'fid' => null,
					'name' => $address->firstname.' '.$address->lastname,
					'phone' => $address->phone ? $address->phone : $address->phone_mobile,
					'postalCode' => DpdPoland::convertPostcode($address->postcode)
				);
			}
			else
			{
				self::$errors[] = $this->l('Customer does not exists');
				return false;
			}
		}
		else
		{
			self::$errors[] = $this->l('Receiver address does not exists');
			return false;
		}

		return true;
	}

	private function prepareSenderAddress($client_number = 'null')
	{
		$settings = new DpdPolandConfiguration;

		$this->sender = array(
			'address' => $settings->address,
			'city' => $settings->city,
			'company' => $settings->company_name,
			'countryCode' => DpdPoland::POLAND_ISO_CODE,
			'email' => $settings->email,
			'fid' => $client_number,
			'name' => $settings->name_surname,
			'phone' => $settings->phone,
			'postalCode' => DpdPoland::convertPostcode($settings->postcode)
		);
	}


	private function prepareServicesData(DpdPolandPackage $package_obj)
	{
		if ($package_obj->cod_amount !== null)
		{
			$this->services['cod'] = array(
				'amount' => $package_obj->cod_amount,
				'currency' => _DPDPOLAND_CURRENCY_ISO_
			);
		}

		if ($package_obj->declaredValue_amount !== null)
		{
			$this->services['declaredValue'] = array(
				'amount' => $package_obj->declaredValue_amount,
				'currency' => _DPDPOLAND_CURRENCY_ISO_
			);
		}
	}

	public function getSenderAddress($client_number)
	{
		if (!$this->sender)
			$this->prepareSenderAddress($client_number);

		return $this->sender;
	}

	public function generateLabels(DpdPolandPackage $package, $outputDocFormat, $outputDocPageFormat, $policy)
	{
		if (!in_array($outputDocPageFormat, array(DpdPolandConfiguration::PRINTOUT_FORMAT_A4, DpdPolandConfiguration::PRINTOUT_FORMAT_LABEL)))
			$outputDocPageFormat = DpdPolandConfiguration::PRINTOUT_FORMAT_A4;

		$this->prepareSenderAddress();

		$params = array(
			'dpdServicesParamsV1' => array(
				'policy' => $policy,
				'session' => array(
					'sessionId' => (int)$package->sessionId,
					'sessionType' => $package->getSessionType()
				)
			),
			'outputDocFormatV1' => $outputDocFormat,
			'outputDocPageFormatV1' => $outputDocPageFormat,
			'pickupAddress' => $this->sender
		);

		if (!$result = $this->generateSpedLabelsV1($params))
			return false;

		if (isset($result['session']) && $result['session']['statusInfo']['status'] == 'OK')
		{
			$package->labels_printed = 1;
			$package->update();
			return $result['documentData'];
		}
		else
		{

			$error = isset($result['session']['packages']) ? $result['session']['packages']['statusInfo']['description'] :
				$result['session']['statusInfo']['description'];
			self::$errors[] = $error;

			return false;
		}
	}

	public function generateLabelsForMultiplePackages($package_ids, $outputDocFormat, $outputDocPageFormat, $policy)
	{
		$sessionType = '';
		$packages = array();

		foreach ($package_ids as $id_package_ws)
		{
			$package = new DpdPolandPackage((int)$id_package_ws);

			if (!$sessionType || $sessionType == $package->getSessionType())
				$sessionType = $package->getSessionType();
			else
			{
				self::$errors[] = $this->l('Manifests of DOMESTIC shipments cannot be mixed with INTERNATIONAL shipments');
				return false;
			}

			$packages[] = array(
				'packageId' => (int)$id_package_ws
			);
		}

		$this->prepareSenderAddress();

		$params = array(
			'dpdServicesParamsV1' => array(
				'policy' => $policy,
				'session' => array(
					'packages' => $packages,
					'sessionType' => $sessionType
				)
			),
			'outputDocFormatV1' => $outputDocFormat,
			'outputDocPageFormatV1' => $outputDocPageFormat,
			'pickupAddress' => $this->sender
		);

		if (!$result = $this->generateSpedLabelsV1($params))
			return false;

		if (isset($result['session']['statusInfo']['status']) && $result['session']['statusInfo']['status'] == 'OK')
		{
			foreach ($packages as $id_package_ws)
			{
				$package = new DpdPolandPackage($id_package_ws);
				$package->labels_printed = 1;
				$package->update();
			}

			return $result['documentData'];
		}
		else
		{
			$packages = $result['session']['statusInfo'];
			$packages = (array_values($packages) === $packages) ? $packages : array($packages); // array must be multidimentional

			foreach ($packages as $package)
				if (isset($package['description']))
					self::$errors[] = $package['description'];
				elseif (isset($package['status']))
					self::$errors[] = $package['status'];

			return false;
		}
	}
}