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
	
class DpdPolandCarrier extends DpdPolandObjectModel
{
	public $id_dpdpoland_carrier;
	
	public $id_carrier;
	
	public $id_reference;
	
	public $date_add;
	
	public $date_upd;
	
	public function __construct($id_dpdpoland_carrier = null)
	{
		parent::__construct($id_dpdpoland_carrier);
	}
	
	public static $definition = array(
		'table' => _DPDPOLAND_CARRIER_DB_,
		'primary' => 'id_dpdpoland_carrier',
		'multilang_shop' => true,
		'multishop' => true,
		'fields' => array(
			'id_dpdpoland_carrier'	=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'id_carrier'			=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'id_reference'			=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'date_add' 				=> 	array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			'date_upd' 				=> 	array('type' => self::TYPE_DATE, 'validate' => 'isDate')
		)
	);
	
	public static function getReferenceByIdCarrier($id_carrier)
	{
		return DB::getInstance()->getValue('
			SELECT `id_reference`
			FROM `'._DB_PREFIX_._DPDPOLAND_CARRIER_DB_.'`
			WHERE `id_carrier` = "'.(int)$id_carrier.'"
		');
	}
	
	public static function getIdCarrierByReference($id_reference)
	{
		return DB::getInstance()->getValue('
			SELECT MAX(`id_carrier`)
			FROM `'._DB_PREFIX_._DPDPOLAND_CARRIER_DB_.'`
			WHERE `id_reference` = "'.(int)$id_reference.'"
		');
	}
}