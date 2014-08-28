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

class DpdPolandPickup extends DpdPolandWS
{
	public $id_pickup;
	public $pickupDate;
	public $pickupTime;
	public $orderType;
	public $dox = false; /*envelope*/
	public $doxCount;
	public $parcels = false;
	public $parcelsCount;
	public $parcelsWeight;
	public $parcelMaxWeight;
	public $parcelMaxHeight;
	public $parcelMaxDepth;
	public $parcelMaxWidth;
	public $pallet = false;
	public $palletsCount;
	public $palletsWeight;
	public $palletMaxWeight;
	public $palletMaxHeight;

	const STANDARD_PARCEL = true;

	/**
	 * Makes web services call to arrange a pickup
	 * @param string $operationType
	 * @param bool $waybillsReady
	 * @return bool
	 */
	public function arrange($operationType = 'INSERT', $waybillsReady = true)
	{
		list($pickupTimeFrom, $pickupTimeTo) = explode('-', $this->pickupTime);

		$settings = new DpdPolandConfiguration;

		$params = array(
			'dpdPickupParamsV3' => array(
				'operationType' => $operationType,
				'orderType' => $this->orderType,
				'pickupCallSimplifiedDetails' => array(
					'packagesParams' => $this->getPackagesParams(),
					'pickupCustomer' => array(
						'customerFullName' => $settings->customer_name,
						'customerName' => $settings->customer_company,
						'customerPhone' => $settings->customer_phone
					),
					'pickupPayer' => array(
						'payerName' => $settings->client_name,
						'payerNumber' => $settings->client_number
					),
					'pickupSender' => array(
						'senderAddress' => $settings->address,
						'senderCity' => $settings->city,
						'senderFullName' => $settings->name_surname,
						'senderName' => $settings->name_surname,
						'senderPhone' => $settings->phone,
						'senderPostalCode' => DpdPoland::convertPostcode($settings->postcode),
					)
				),
				'pickupDate' => $this->pickupDate,
				'pickupTimeFrom' => $pickupTimeFrom,
				'pickupTimeTo' => $pickupTimeTo,
				'waybillsReady' => $waybillsReady
			)
		);

		$result = $this->packagesPickupCallV3($params);

		if (isset($result['statusInfo']) && isset($result['statusInfo']['errorDetails']))
		{
			$errors = $result['statusInfo']['errorDetails'];
			$errors = (array_values($errors) === $errors) ? $errors : array($errors); // array must be multidimentional
			foreach ($errors as $error)
				self::$errors[] = sprintf($this->l('Error code: %s, fields: %s'), $error['code'], $error['fields']);

			return false;
		}

		if (isset($result['orderNumber']))
		{
			$this->id_pickup = (int)$result['orderNumber'];
			return true;
		}
		self::$errors[] = $this->l('Order number is undefined');

		return false;
	}

	/**
	 * Create array with pickup packages (envelopes, pallets or parcels) data for web services call
	 * @return array
	 */
	private function getPackagesParams()
	{
		$result = array_merge(
			$this->getEnvelopesParams(),
			$this->getPalletsParams(),
			$this->getParcelsParams()
		);
		return $result;
	}

	/**
	 * Returns array with envelopes data prepared for web services call
	 * In order to send envelopes, both conditions must be met:
	 * 	1. Envelopes chosen
	 * 	2. Envelopes count > 0
	 * Otherwise envelopes will be set as false and count will be 0
	 * @return array
	 */
	private function getEnvelopesParams()
	{
		$result =  array(
			'dox' => 0,
			'doxCount' => 0
		);

		if ($this->dox && (int)$this->doxCount)
		{
			$result['dox'] = 0; // always false even if envelopes are sent
			$result['doxCount'] = (int)$this->doxCount;
		}
		return $result;
	}

	/**
	 * Returns array with envelopes data prepared for web services call
	 * In order to send pallets, both conditions must be met:
	 * 	1. Pallets chosen
	 * 	2. Pallets count > 0
	 * @return array
	 */
	private function getPalletsParams()
	{
		$result = array(
			'pallet' => 0,
			'palletMaxHeight' => '',
			'palletMaxWeight' => '',
			'palletsCount' => 0,
			'palletsWeight' => ''
		);

		if ($this->pallet && (int)$this->palletsCount)
		{
			$result['pallet'] = 1;
			$result['palletMaxHeight'] = $this->palletMaxHeight;
			$result['palletMaxWeight'] = $this->palletMaxWeight;
			$result['palletsCount'] = (int)$this->palletsCount;
			$result['palletsWeight'] = $this->palletsWeight;
		}
		return $result;
	}

	/**
	 * Returns array with parcels data prepared for web services call
	 * If envelopes or pallets are sent without parcels then parcels should have all params set to 1
	 * In order to send parcels, both conditions must be met:
	 * 	1. Parcels chosen
	 * 	2. Parcels count > 0
	 * @return array
	 */
	private function getParcelsParams()
	{
		$result = array(
			'parcelsCount' => 0,
			'standardParcel' => self::STANDARD_PARCEL, // Always must be true
			'parcelMaxDepth' => '',
			'parcelMaxHeight' => '',
			'parcelMaxWeight' => '',
			'parcelMaxWidth' => '',
			'parcelsWeight' => ''
		);

		// If no parcels but envelopes or pallets are chosen then parcels all values should be 1
		if (!$this->parcels && ($this->dox || $this->pallet))
		{
			$result['parcelsCount'] = 1;
			$result['standardParcel'] = self::STANDARD_PARCEL; // Always must be true
			$result['parcelMaxDepth'] = 1;
			$result['parcelMaxHeight'] = 1;
			$result['parcelMaxWeight'] = 1;
			$result['parcelMaxWidth'] = 1;
			$result['parcelsWeight'] = 1;
		}
		elseif ($this->parcels && (int)$this->parcelsCount)
		{
			$result['parcelsCount'] = (int)$this->parcelsCount;
			$result['standardParcel'] = self::STANDARD_PARCEL; // Always must be true
			$result['parcelMaxDepth'] = $this->parcelMaxDepth;
			$result['parcelMaxHeight'] = $this->parcelMaxHeight;
			$result['parcelMaxWeight'] = $this->parcelMaxWeight;
			$result['parcelMaxWidth'] = $this->parcelMaxWidth;
			$result['parcelsWeight'] = $this->parcelsWeight;
		}
		return $result;
	}

	/**
	 * Get available pickup time frames for a particular date
	 * @return bool
	 */
	public function getCourierTimeframes()
	{
		$settings = new DpdPolandConfiguration;

		$params = array(
			'senderPlaceV1' => array(
				'countryCode' => DpdPoland::POLAND_ISO_CODE,
				'zipCode' => DpdPoland::convertPostcode($settings->postcode)
			)
		);

		$result = $this->getCourierOrderAvailabilityV1($params);

		if (!isset($result['ranges']) && !self::$errors)
			self::$errors[] = $this->l('Cannot get TimeFrames from webservices. Please check if sender\'s postal code is typed in correctly');

		return (isset($result['ranges'])) ? $result['ranges'] : false;
	}
}