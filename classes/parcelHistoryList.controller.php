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


class DpdPolandParcelHistoryController extends DpdPolandController
{
	const DEFAULT_ORDER_BY 	= 'date_add';
	const DEFAULT_ORDER_WAY = 'desc';

	private $tracking_link = 'http://www.dpd.com.pl/tracking.asp?przycisk.x=14&przycisk.y=6&przycisk=Wyszukaj&przycisk=Wyszukaj&ID_kat=3&ID=33&Mark=18&p1=';

	public function getList()
	{
		$keys_array = array('id_order', 'id_parcel', 'receiver', 'country', 'postcode', 'city', 'address', 'date_add');
		$this->prepareListData($keys_array, 'ParcelHistories', new DpdPolandParcel(),
			self::DEFAULT_ORDER_BY, self::DEFAULT_ORDER_WAY, 'parcel_history_list');
		$this->context->smarty->assign('tracking_link', $this->tracking_link);
		return $this->context->smarty->fetch(_DPDPOLAND_TPL_DIR_.'admin/parcel_history_list.tpl');
	}
}