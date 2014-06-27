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

class Help extends AdminTab
{
	public function __construct()
	{
		Tools::redirectAdmin('index.php?tab=AdminModules&token='.Tools::getAdminTokenLite('AdminModules').
			'&configure=dpdpoland&menu=help&tab_module=dpdpoland&module_name=dpdpoland');
	}
}