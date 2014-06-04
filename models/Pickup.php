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
    public $dox = false; // envelope
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
	
	const standardParcel = true;

	public function arrange($operationType = 'INSERT', $waybillsReady = true)
	{
		list($pickupTimeFrom, $pickupTimeTo) = explode('-', $this->pickupTime);
		
		$settings = new DpdPolandConfiguration;

		$params = array(
			'dpdPickupParamsV3' => array(
				'operationType' => pSQL($operationType),
				'orderType' => pSQL($this->orderType),
				'pickupCallSimplifiedDetails' => array(
					'packagesParams' => $this->getPackagesParams(),
					'pickupCustomer' => array(
						'customerFullName' => pSQL($settings->customer_name),
						'customerName' => pSQL($settings->customer_company),
						'customerPhone' => pSQL($settings->customer_phone)
					),
					'pickupPayer' => array(
						//'payerCostCenter' => null,
						'payerName' => pSQL($settings->client_name),
						'payerNumber' => pSQL($settings->client_number)
					),
					'pickupSender' => array(
						'senderAddress' => pSQL($settings->address),
						'senderCity' => pSQL($settings->city),
						'senderFullName' => pSQL($settings->name_surname),
						'senderName' => pSQL($settings->name_surname),
						'senderPhone' => pSQL($settings->phone),
						'senderPostalCode' => DpdPoland::convertPostcode(pSQL($settings->postcode)),
					)
				),
				'pickupDate' => pSQL($this->pickupDate),
				'pickupTimeFrom' => pSQL($pickupTimeFrom),
				'pickupTimeTo' => pSQL($pickupTimeTo),
				'waybillsReady' => pSQL($waybillsReady)
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
	
	private function getPackagesParams()
	{
		$parcels_count = $this->parcelsCount > 0 ? $this->parcelsCount : 1;
		
		$packagesParams = array(
			'dox' => 1,//pSQL($this->dox),
			'doxCount' => (int)$this->doxCount,
			'pallet' => pSQL($this->pallet),
			'palletMaxHeight' => pSQL($this->palletMaxHeight),
			'palletMaxWeight' => pSQL($this->palletMaxWeight),
			'palletsCount' => (int)$this->palletsCount,
			'palletsWeight' => pSQL($this->palletsWeight),
			'parcelsCount' => (int)$parcels_count,
			'standardParcel' => self::standardParcel,
			'parcelMaxDepth' => $this->parcelMaxDepth ? pSQL($this->parcelMaxDepth) : 1,
			'parcelMaxHeight' => $this->parcelMaxHeight ? pSQL($this->parcelMaxHeight) : 1,
			'parcelMaxWeight' => $this->parcelMaxWeight ? pSQL($this->parcelMaxWeight) : 1,
			'parcelMaxWidth' => $this->parcelMaxWidth ? pSQL($this->parcelMaxWidth) : 1,
			'parcelsWeight' => $this->parcelsWeight ? pSQL($this->parcelsWeight) : 1
		);
		
		return $packagesParams;
	}
	
	public function getCourierTimeframes()
	{
		$settings = new DpdPolandConfiguration;
		
        $params = array(
			'senderPlaceV1' => array(
			   'countryCode' => DpdPoland::POLAND_ISO_CODE,
			   'zipCode' => pSQL(DpdPoland::convertPostcode($settings->postcode))
			)
		);
		
		$result = $this->getCourierOrderAvailabilityV1($params);
		
		if (!isset($result['ranges']) && !self::$errors)
			self::$errors[] = $this->l('Cannot get TimeFrames from webservices. Please check if sender\'s postal code is typed in correctly');
		
		return (isset($result['ranges'])) ? $result['ranges'] : false;
	}
}