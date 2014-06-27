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


class DpdPolandService
{
	const IMG_DIR = 'DPD_services';
	const IMG_EXTENTION = 'jpg';

	const CONTINENT_EUROPE = 1;
	const CONTINENT_NORTH_AMERICA = 0;
	const CONTINENT_ASIA = 0;
	const CONTINENT_AFRICA = 0;
	const CONTINENT_OCEANIA = 0;
	const CONTINENT_SOUTH_AMERICA = 0;
	const CONTINENT_EUROPE_EU = 1;
	const CONTINENT_CENTRAL_AMERICA = 0;

	protected $module_instance;
	protected $continents;

	public function __construct()
	{
		$this->module_instance = Module::getInstanceByName('dpdpoland');
		$this->continents = array(
			'1' => self::CONTINENT_EUROPE,
			'2' => self::CONTINENT_NORTH_AMERICA,
			'3' => self::CONTINENT_ASIA,
			'4' => self::CONTINENT_AFRICA,
			'5' => self::CONTINENT_OCEANIA,
			'6' => self::CONTINENT_SOUTH_AMERICA,
			'7' => self::CONTINENT_EUROPE_EU,
			'8' => self::CONTINENT_CENTRAL_AMERICA,
		);
	}

	/**
	 * Delete existing carrier
	 *
	 * @param int $id_carrier ID of carrier to delete
	 * @return boolean Delete is ok or not
	 */
	protected static function deleteCarrier($id_carrier)
	{
		if (!$id_carrier)
			return true;

		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			$id_carrier = (int)DpdPolandCarrier::getIdCarrierByReference((int)$id_carrier);
			$carrier = new Carrier((int)$id_carrier);
		}
		else
			$carrier = Carrier::getCarrierByReference($id_carrier);

		if (!Validate::isLoadedObject($carrier))
			return true;

		if ($carrier->deleted)
			return true;

		$carrier->deleted = 1;
		return (bool)$carrier->save();
	}

	protected static function setGroups14($id_carrier, $groups)
	{
		foreach ($groups as $id_group)
			if (!Db::getInstance()->execute('
				INSERT INTO `'._DB_PREFIX_.'carrier_group`
					(`id_carrier`, `id_group`)
				VALUES
					("'.(int)$id_carrier.'", "'.(int)$id_group.'")
			'))
				return false;
		return true;
	}
}