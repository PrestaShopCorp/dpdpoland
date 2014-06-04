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

class logo extends AdminTab
{
	private $module_instance;
	
	const FILENAME = 'logo';
	
	public function __construct()
	{
		parent::__construct();
		
		$this->module_instance = Module::getInstanceByName('dpdpoland');
		$this->context = Context::getContext();
	}
	
	public function postProcess()
	{
		parent::postProcess();
	}
	
	public function display()
	{
		$this->module_instance->addCSS(_DPDPOLAND_CSS_URI_.'backoffice.css');
		$this->module_instance->addCSS(_DPDPOLAND_CSS_URI_.'toolbar.css');
		$this->context->smarty->assign(array(
			'module_link' => $this->module_instance->module_url.'&token='.Tools::getAdminTokenLite('AdminModules'),
			'breadcrumb' => array($this->module_instance->displayName),
			
		));
		echo $this->context->smarty->fetch(_DPDPOLAND_TPL_DIR_.'admin/navigation.tpl');
	}
}