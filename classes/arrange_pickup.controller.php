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

class DpdPolandArrangePickUpController extends DpdPolandController
{
	const FILENAME = 'arrange_pickup.controller';
	
	private $data = array();
	
	private $rules = array();
	
	public function __construct()
	{
		parent::__construct();
		
		$this->rules = array(
			'pickupDate' => array(
				'validate' => 'isDate',
				'fieldname' => $this->l('Date of pickup'),
				'required' => true
			),
			'pickupTime' => array(
				'validate' => 'isAnything',
				'fieldname' => $this->l('Timeframe of pickup'),
				'required' => true
			),
			'orderType' => array(
				'validate' => 'isAnything',
				'fieldname' => $this->l('Shipment type'),
				'required' => true
			),
			'orderContent' => array(
				'validate' => 'isAnything',
				'fieldname' => $this->l('General shipment content'),
				'required' => false
			),
			'dox' => array(
				'validate' => 'isAnything',
				'fieldname' => $this->l('Envelopes'),
				'required' => false
			),
			'doxCount' => array(
				'validate' => 'isUnsignedInt',
				'fieldname' => $this->l('Number of envelopes'),
				'required' => true,
				'dependency' => 'dox'
			),
			'parcels' => array(
				'validate' => 'isUnsignedInt',
				'fieldname' => $this->l('Parcels'),
				'required' => false
			),
			'parcelsCount' => array(
				'validate' => 'isUnsignedInt',
				'fieldname' => $this->l('Number of parcels'),
				'required' => true,
				'dependency' => 'parcels'
			),
			'parcelsWeight' => array(
				'validate' => 'isUnsignedFloat',
				'fieldname' => $this->l('Summary weight'),
				'required' => true,
				'dependency' => 'parcels'
			),
			'parcelMaxWeight' => array(
				'validate' => 'isUnsignedFloat',
				'fieldname' => $this->l('Weight of the heaviest item'),
				'required' => true,
				'dependency' => 'parcels'
			),
			'parcelMaxHeight' => array(
				'validate' => 'isUnsignedFloat',
				'fieldname' => $this->l('Height of the tallest item'),
				'required' => true,
				'dependency' => 'parcels'
			),
			'parcelMaxDepth' => array(
				'validate' => 'isUnsignedFloat',
				'fieldname' => $this->l('Length of the largest item'),
				'required' => true,
				'dependency' => 'parcels'
			),
			'parcelMaxWidth' => array(
				'validate' => 'isUnsignedFloat',
				'fieldname' => $this->l('Width of the longest item'),
				'required' => true,
				'dependency' => 'parcels'
			),
			'pallet' => array(
				'validate' => 'isAnything',
				'fieldname' => $this->l('Pallets'),
				'required' => false
			),
			'palletsCount' => array(
				'validate' => 'isUnsignedInt',
				'fieldname' => $this->l('Number of pallets'),
				'required' => true,
				'dependency' => 'pallet'
			),
			'palletsWeight' => array(
				'validate' => 'isUnsignedFloat',
				'fieldname' => $this->l('Summary weight'),
				'required' => true,
				'dependency' => 'pallet'
			),
			'palletMaxWeight' => array(
				'validate' => 'isUnsignedFloat',
				'fieldname' => $this->l('Weight of the heaviest item'),
				'required' => true,
				'dependency' => 'pallet'
			),
			'palletMaxHeight' => array(
				'validate' => 'isUnsignedFloat',
				'fieldname' => $this->l('Height of the tallest item'),
				'required' => true,
				'dependency' => 'pallet'
			)
		);
	}
	
	public function __get($name)
	{
		return isset($this->data[$name]) ? $this->data[$name] : null;
	}
	
	public static function isWeekend($date)
	{
		return (date('N', strtotime($date)) >= 6);
	}
	
	public function getPage()
	{
		$date = date('Y-m-d');
		$pickupDate = (Tools::getValue('pickupDate') ? Tools::getValue('pickupDate') : ($this->isWeekend($date) ? date('Y-m-d', strtotime('next monday')) : $date));

		$this->context->smarty->assign(array(
			'settings' => new DpdPolandConfiguration,
			'pickupDate' => $pickupDate
		));
		return $this->context->smarty->fetch(_DPDPOLAND_TPL_DIR_.'admin/arrange_pickup.tpl');
	}
	
	public static function validateTimeframes(&$timeframes, $poland_time_in_seconds, $is_today)
	{
		$count_timeframes = count($timeframes);
		for ($i = 0; $i < $count_timeframes; $i++)
		{
			if (!isset($timeframes[$i]['range']))
			{
				unset($timeframes[$i]);
				continue;
			}
			
			$end_time = explode('-', $timeframes[$i]['range']);
			$end_time = strtotime($end_time[1]);
			if ($is_today && round(abs($end_time - $poland_time_in_seconds) / 60) < 120)
				unset($timeframes[$i]);
		}
	}
	
	public static function createExtraTimeframe($pickup_timeframes)
	{
		if (!$pickup_timeframes || !isset($pickup_timeframes[0]['range']))
			return false;
		
		$extraTimeFrom = null;
		$extraTimeTo = null;
		
		foreach ($pickup_timeframes as $frame)
		{
			if (isset($frame['range']))
			{
				list($pickupTimeFrom, $pickupTimeTo) = explode('-', $frame['range']);
				if (!$extraTimeFrom || $extraTimeFrom && (str_replace(':', '', $pickupTimeFrom) < str_replace(':', '', $extraTimeFrom)))
					$extraTimeFrom = $pickupTimeFrom;
				if (!$extraTimeTo || $pickupTimeTo && (str_replace(':', '', $pickupTimeTo) > str_replace(':', '', $extraTimeTo)))
					$extraTimeTo = $pickupTimeTo;
			}
		}
		
		if (!$extraTimeFrom || !$extraTimeTo)
			return false;
		
		if ($extraTimeFrom.'-'.$extraTimeTo == $pickup_timeframes[0]['range'])
			return false;
		
		return $extraTimeFrom.'-'.$extraTimeTo;
	}
	
	public static function init($module_instance)
	{
		if (Tools::isSubmit('requestPickup'))
		{
			$controller = new DpdPolandArrangePickUpController;
			
			$data = $controller->getData();
			
			if ($controller->validate())
			{
				$pickup = new DpdPolandPickup;
				
				foreach($data as $element => $value)
					$pickup->$element = $value;

				if (!$pickup->arrange())
					$module_instance->outputHTML($module_instance->displayError(reset(DpdPolandPickup::$errors)));
				else
				{
					DpdPoland::addFlashMessage(sprintf($module_instance->l('Pickup was successfully arranged. Number of order is: %d', self::FILENAME), $pickup->id_pickup));
					Tools::redirectAdmin($module_instance->module_url.'&menu=arrange_pickup');
				}
			}
			else
				$module_instance->outputHTML($module_instance->displayError(reset(self::$errors)));
		}
	}
	
	public function getData()
	{
		if (!$this->data)
			foreach (array_keys($this->rules) as $element)
				$this->data[$element] = Tools::getValue($element);
		
		return $this->data;
	}
	
	public function validate()
	{
		$date = Tools::getValue('pickupDate');
		
		if (!Validate::isDateFormat($date))
		{
			self::$errors[] = $this->l('Wrong date format');
			return false;
		}
		
		if (strtotime($date) < strtotime(date('Y-m-d')))
		{
			self::$errors[] = $this->l('Date can not be earlier than').' '.date('Y-m-d');
			return false;
		}
		
		if ($this->isWeekend($date))
		{
			self::$errors[] = $this->l('Weekends can not be chosen');
			return false;
		}
		
		if (!$this->dox && !$this->parcels && !$this->pallet)
		{
			self::$errors[] = $this->l('At least one service must be selected');
			return false;
		}

		
		foreach ($this->rules as $element => $rules)
		{
			if (!isset($rules['dependency']) || (isset($rules['dependency']) && $this->{$rules['dependency']}))
			{
				if ($rules['required'] && !$this->$element)
				{
					self::$errors[] = sprintf($this->l('The "%s" field is required.'), $rules['fieldname']);
					return false;
				}
				elseif($this->$element && method_exists('Validate', $rules['validate']) && !call_user_func(array('Validate', $rules['validate']), $this->$element))
				{
					self::$errors[] = sprintf($this->l('The "%s" field is invalid.'), $rules['fieldname']);
					return false;
				}
			}
		}
		
		return true;
	}
}