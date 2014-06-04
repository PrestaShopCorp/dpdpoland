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

class DpdPolandLanguage
{
	const FILENAME = 'dpdpoland.lang';
	
	private $translations = array();
	private $module_instance;
	
	public function __construct()
	{
		$this->module_instance = Module::getInstanceByName('dpdpoland');
		
		$this->translations = array(
			'1000' => $this->module_instance->l('ERROR_INCORRECT_WEIGHT_FOR_DOX', self::FILENAME),
			'1002' => $this->module_instance->l('ERROR_INCORRECT_WEIGHT_FOR_COUNTRY', self::FILENAME),
			'1003' => $this->module_instance->l('ERROR_INCORRECT_PARCELS_COUNT_FOR_DOX', self::FILENAME),
			'1004' => $this->module_instance->l('ERROR_GUARANTEE_TIMEFIXED_OUT_OF_RANGE', self::FILENAME),
			'1005' => $this->module_instance->l('ERROR_DECLARED_VALUE_AMOUNT_OUT_OF_RANGE', self::FILENAME),
			'1008' => $this->module_instance->l('ERROR_COD_AMOUNT_OUT_OF_RANGE', self::FILENAME),
			'1009' => $this->module_instance->l('ERROR_INCORRECT_COD_CURRENCY_FOR_PL', self::FILENAME),
			'1011' => $this->module_instance->l('ERROR_INCORRECT_WEIGHT', self::FILENAME),
			'1013' => $this->module_instance->l('ERROR_PALLET_NOT_AVAILABLE_FOR_COUNTRY', self::FILENAME),
			'1014' => $this->module_instance->l('ERROR_DUTY_NOT_AVAILABLE_FOR_COUNTRY', self::FILENAME),
			'1015' => $this->module_instance->l('ERROR_CARRY_IN_NOT_AVAILABLE_FOR_PAYER_FID', self::FILENAME),
			'1016' => $this->module_instance->l('ERROR_COD_NOT_AVAILABLE_FOR_COUNTRY', self::FILENAME),
			'1017' => $this->module_instance->l('ERROR_ROD_NOT_AVAILBLE_FOR_COUNTRY', self::FILENAME),
			'1018' => $this->module_instance->l('ERROR_CUD_NOT_AVAILABLE_FOR_COUNTRY', self::FILENAME),
			'1019' => $this->module_instance->l('ERROR_CARRY_IN_NOT_AVAILABLE_FOR_COUNTRY', self::FILENAME),
			'1020' => $this->module_instance->l('ERROR_IN_PERS_NOT_AVAILABLE_FOR_COUNTRY', self::FILENAME),
			'1021' => $this->module_instance->l('ERROR_PRIV_PERS_NOT_AVAILABLE_FOR_COUNTRY', self::FILENAME),
			'1022' => $this->module_instance->l('ERROR_DOX_NOT_AVAILABLE_FOR_COUNTRY', self::FILENAME),
			'1023' => $this->module_instance->l('ERROR_SELF_COL_NOT_AVAILABLE_FOR_COUNTRY', self::FILENAME),
			'1024' => $this->module_instance->l('ERROR_GUARANTEE_TIME0930_NOT_AVAILABLE_FOR_COUNTRY', self::FILENAME),
			'1025' => $this->module_instance->l('ERROR_GUARANTEE_TIME1200_NOT_AVAILABLE_FOR_COUNTRY', self::FILENAME),
			'1026' => $this->module_instance->l('ERROR_GUARANTEE_TIMEFIXED_NOT_AVAILABLE_FOR_COUNTRY', self::FILENAME),
			'1028' => $this->module_instance->l('ERROR_GUARANTEE_SATURDAY_NOT_AVAILABLE_FOR_COUNTRY', self::FILENAME),
			'1030' => $this->module_instance->l('ERROR_GUARANTEE_B2C_NOT_AVAILABLE_FOR_COUNTRY', self::FILENAME),
			'1032' => $this->module_instance->l('ERROR_GUARANTEE_TIME0930_NOT_AVAILABLE_FOR_POSTAL_CODE', self::FILENAME),
			'1033' => $this->module_instance->l('ERROR_GUARANTEE_TIME1200_NOT_AVAILABLE_FOR_POSTAL_CODE', self::FILENAME),
			'1034' => $this->module_instance->l('ERROR_GUARANTEE_INTER_NOT_AVAILABLE_FOR_POSTAL_CODE', self::FILENAME),
			'1036' => $this->module_instance->l('ERROR_PALLET_NOT_AVAILABLE_FOR_POSTAL_CODE', self::FILENAME),
			'1037' => $this->module_instance->l('ERROR_CARRYIN_NOT_AVAILABLE_FOR_POSTAL_CODE', self::FILENAME),
			'1038' => $this->module_instance->l('ERROR_GUARANTEE_B2C_NOT_AVAILABLE_FOR_PAYER_FID', self::FILENAME),
			'1039' => $this->module_instance->l('ERROR_INCORRECT_GUARANTEE_B2C_RANGE', self::FILENAME),
			'1042' => $this->module_instance->l('ERROR_INCORRECT_PAYMENT_TYPE', self::FILENAME),
			'1043' => $this->module_instance->l('ERROR_INCORRECT_PARCELS_COUNT_FOR_CARRYIN', self::FILENAME),
			'1044' => $this->module_instance->l('ERROR_INCORRECT_WEIGHT_FOR_CARRYIN', self::FILENAME),
			'1045' => $this->module_instance->l('ERROR_PARCEL_SIZE_X_OUT_OF_RANGE', self::FILENAME),
			'1046' => $this->module_instance->l('ERROR_PARCEL_SIZE_Y_OUT_OF_RANGE', self::FILENAME),
			'1047' => $this->module_instance->l('ERROR_PARCEL_SIZE_Z_OUT_OF_RANGE', self::FILENAME),
			'1048' => $this->module_instance->l('ERROR_PARCEL_SIZES_EXCEEDED_DIMENISIONS_80X120X180', self::FILENAME),
			'1049' => $this->module_instance->l('ERROR_INT_PARCEL_SIZES_EXCEEDED_300', self::FILENAME),
			'1051' => $this->module_instance->l('ERROR_PARCEL_SIZES_EXCEEDED_300', self::FILENAME),
			'1052' => $this->module_instance->l('ERROR_PARCEL_CAPACITY_EXCEEDED', self::FILENAME),
			'1053' => $this->module_instance->l('ERROR_PARCEL_SIZE_X_EXCEEDED', self::FILENAME),
			'1054' => $this->module_instance->l('ERROR_PARCEL_SIZE_Y_EXCEEDED', self::FILENAME),
			'1055' => $this->module_instance->l('ERROR_PARCEL_SIZE_Z_EXCEEDED', self::FILENAME),
			'1056' => $this->module_instance->l('ERROR_RECEIVER_NAME_MAX_SIZE_EXCEEDED', self::FILENAME),
			'1057' => $this->module_instance->l('ERROR_RECEIVER_COMPANY_MAX_SIZE_EXCEEDED', self::FILENAME),
			'1058' => $this->module_instance->l('ERROR_RECEIVER_ADDRESS_MAX_SIZE_EXCEEDED', self::FILENAME),
			'1059' => $this->module_instance->l('ERROR_RECEIVER_CITY_MAX_SIZE_EXCEEDED', self::FILENAME),
			'1060' => $this->module_instance->l('ERROR_RECEIVER_EMAIL_MAX_SIZE_EXCEEDED', self::FILENAME),
			'1061' => $this->module_instance->l('ERROR_RECEIVER_PHONE_MAX_SIZE_EXCEEDED', self::FILENAME),
			'1062' => $this->module_instance->l('ERROR_EMPTY_RECEIVER_ADDRESS', self::FILENAME),
			'1063' => $this->module_instance->l('ERROR_EMPTY_RECEIVER_CITY', self::FILENAME),
			'1064' => $this->module_instance->l('ERROR_EMPTY_RECEIVER_NAME_AND_COMPANY', self::FILENAME),
			'1065' => $this->module_instance->l('ERROR_SENDER_NAME_MAX_SIZE_EXCEEDED', self::FILENAME),
			'1066' => $this->module_instance->l('ERROR_SENDER_COMPANY_MAX_SIZE_EXCEEDED', self::FILENAME),
			'1067' => $this->module_instance->l('ERROR_SENDER_ADDRESS_MAX_SIZE_EXCEEDED', self::FILENAME),
			'1068' => $this->module_instance->l('ERROR_SENDER_CITY_MAX_SIZE_EXCEEDED', self::FILENAME),
			'1069' => $this->module_instance->l('ERROR_SENDER_EMAIL_MAX_SIZE_EXCEEDED', self::FILENAME),
			'1070' => $this->module_instance->l('ERROR_SENDER_PHONE_MAX_SIZE_EXCEEDED', self::FILENAME),
			'1071' => $this->module_instance->l('ERROR_EMPTY_SENDER_ADDRESS', self::FILENAME),
			'1072' => $this->module_instance->l('ERROR_EMPTY_SENDER_CITY', self::FILENAME),
			'1073' => $this->module_instance->l('ERROR_EMPTY_SENDER_NAME_AND_COMPANY', self::FILENAME),
			'1074' => $this->module_instance->l('ERROR_REF1_MAX_SIZE_EXCEEDED', self::FILENAME),
			'1075' => $this->module_instance->l('ERROR_REF2_MAX_SIZE_EXCEEDED', self::FILENAME),
			'1076' => $this->module_instance->l('ERROR_REF3_MAX_SIZE_EXCEEDED', self::FILENAME),
			'1077' => $this->module_instance->l('ERROR_PARCEL_CONTENT_MAX_SIZE_EXCEEDED', self::FILENAME),
			'1078' => $this->module_instance->l('ERROR_PARCEL_CUSTOMER_DATA1_MAX_SIZE_EXCEEDED', self::FILENAME),
			'1079' => $this->module_instance->l('ERROR_PARCEL_CUSTOMER_DATA2_MAX_SIZE_EXCEEDED', self::FILENAME),
			'1080' => $this->module_instance->l('ERROR_PARCEL_CUSTOMER_DATA3_MAX_SIZE_EXCEEDED', self::FILENAME),
			'1081' => $this->module_instance->l('ERROR_INCORRECT_SENDER_COUNTRY', self::FILENAME),
			'1082' => $this->module_instance->l('ERROR_INCORRECT_RECEIVER_COUNTRY', self::FILENAME),
			'1085' => $this->module_instance->l('ERROR_GUARANTEE_TIMEFIXED_INCORRECT_FORMAT', self::FILENAME),
			'1086' => $this->module_instance->l('ERROR_CARRY_IN_FOR_GABARIT_WEIGHT', self::FILENAME),
			'1087' => $this->module_instance->l('ERROR_IN_PERS_REQUIRES_RECEIVER_NAME', self::FILENAME),
			'1088' => $this->module_instance->l('ERROR_PALLET_INCORRECT_FOR_WEIGHT', self::FILENAME),
			'2005' => $this->module_instance->l('ERROR_DEDICATED_DELIVERY_CUD_EXCLUDING', self::FILENAME),
			'2006' => $this->module_instance->l('ERROR_DEDICATED_DELIVERY_CARRY_IN_EXCLUDING', self::FILENAME),
			'2007' => $this->module_instance->l('ERROR_DEDICATED_DELIVERY_COD_EXCLUDING', self::FILENAME),
			'2008' => $this->module_instance->l('ERROR_DEDICATED_DELIVERY_DECL_VALUE_EXCLUDING', self::FILENAME),
			'2009' => $this->module_instance->l('ERROR_DEDICATED_DELIVERY_DOX_EXCLUDING', self::FILENAME),
			'2010' => $this->module_instance->l('ERROR_DEDICATED_DELIVERY_DUTY_EXCLUDING', self::FILENAME),
			'2011' => $this->module_instance->l('ERROR_DEDICATED_DELIVERY_IN_PERS_EXCLUDING', self::FILENAME),
			'2012' => $this->module_instance->l('ERROR_DEDICATED_DELIVERY_PALLET_EXCLUDING', self::FILENAME),
			'2013' => $this->module_instance->l('ERROR_DEDICATED_DELIVERY_PRIV_PERS_EXCLUDING', self::FILENAME),
			'2014' => $this->module_instance->l('ERROR_DEDICATED_DELIVERY_ROD_EXCLUDING', self::FILENAME),
			'2015' => $this->module_instance->l('ERROR_DEDICATED_DELIVERY_SELF_COL_EXCLUDING', self::FILENAME),
			'2016' => $this->module_instance->l('ERROR_DEDICATED_DELIVERY_TIME0930_EXCLUDING', self::FILENAME),
			'2017' => $this->module_instance->l('ERROR_DEDICATED_DELIVERY_TIME1200_EXCLUDING', self::FILENAME),
			'2018' => $this->module_instance->l('ERROR_DEDICATED_DELIVERY_TIMEFIXED_EXCLUDING', self::FILENAME),
			'2019' => $this->module_instance->l('ERROR_DEDICATED_DELIVERY_OVERTIME2_EXCLUDING', self::FILENAME),
			'2020' => $this->module_instance->l('ERROR_DEDICATED_DELIVERY_B2C_EXCLUDING', self::FILENAME),
			'2021' => $this->module_instance->l('ERROR_DEDICATED_DELIVERY_TIRES_EXCLUDING', self::FILENAME)
		);
	}
	
	public function getTranslation($id_translation)
	{
		if (isset($this->translations[(int)$id_translation]))
			return $this->translations[(int)$id_translation];
		return '';
	}
}