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

class DpdPolandController
{
	protected $context;

	protected $module_instance;

	protected $pagination = array(10, 20, 50, 100, 300);

	public static $errors = array();

	public static $notices = array();

	private $child_class_name;

	public function __construct()
	{
		$this->context = Context::getContext();
		$this->module_instance = Module::getInstanceByName('dpdpoland');
		$this->child_class_name = get_class($this);
	}

	protected function l($text)
	{
		$child_class_name = $this->child_class_name;
		return $this->module_instance->l($text, $child_class_name::FILENAME);
	}

	protected function getFilterQuery($keys_array = array(), $table)
	{
		$sql = '';

		foreach ($keys_array as $key)
			if ($this->context->cookie->__isset($table.'Filter_'.$key))
			{
				$value = $this->context->cookie->{$table.'Filter_'.$key};
				if (Validate::isSerializedArray($value))
				{
					if (version_compare(_PS_VERSION_, '1.5', '<'))
						$date = unserialize($value);
					else
						$date = Tools::unSerialize($value);

					if (!empty($date[0]))
						$sql .= '`'.bqSQL($key).'` > "'.pSQL($date[0]).'" AND ';

					if (!empty($date[1]))
						$sql .= '`'.bqSQL($key).'` < "'.pSQL($date[1]).'" AND ';
				}
				else
				{
					if ($value != '')
						$sql .= '`'.bqSQL($key).'` LIKE "%'.pSQL($value).'%" AND ';
				}
			}

		if ($sql)
			$sql = ' HAVING '.Tools::substr($sql, 0, -4); // remove 'AND ' from the end of query

		return $sql;
	}

	public function prepareListData($keys_array, $table, $model, $default_order_by, $default_order_way, $menu_page)
	{
		if (Tools::isSubmit('submitFilterButton'.$table))
			foreach ($_POST as $key => $value)
			{
				if (strpos($key, $table.'Filter_') !== false) // looking for filter values in $_POST
				{
					if (is_array($value))
						$this->context->cookie->$key = serialize($value);
					else
						$this->context->cookie->$key = pSQL($value);
				}
			}

		if (Tools::isSubmit('submitReset'.$table))
		{
			foreach ($keys_array as $key)
			{
				if ($this->context->cookie->__isset($table.'Filter_'.$key))
				{
					$this->context->cookie->__unset($table.'Filter_'.$key);
					$_POST[$table.'Filter_'.$key] = null;
				}
			}
		}

		$page = (int)Tools::getValue('submitFilter'.$table);
		if (!$page)
			$page = 1;

		$selected_pagination = (int)Tools::getValue('pagination', $this->pagination[0]);
		$start = ($selected_pagination * $page) - $selected_pagination;

		$order_by = Tools::getValue($table.'OrderBy', $default_order_by);
		$order_way = Tools::getValue($table.'OrderWay', $default_order_way);

		$filter = $this->getFilterQuery($keys_array, $table);

		$table_data = $model->getList($order_by, $order_way, $filter, $start, $selected_pagination);
		$list_total = count($model->getList($order_by, $order_way, $filter, null, null));

		$total_pages = ceil($list_total / $selected_pagination);

		if (!$total_pages)
			$total_pages = 1;

		$this->context->smarty->assign(array(
			'full_url' 				=> $this->module_instance->module_url.'&menu='.$menu_page.'&'.$table.'OrderBy='.$order_by.'&'.$table.'OrderWay='.$order_way,
			'table_data' => $table_data,
			'page' => $page,
			'selected_pagination' => $selected_pagination,
			'pagination' => $this->pagination,
			'total_pages' => $total_pages,
			'list_total' => $list_total,
			'order_by' => $order_by,
			'order_way' => $order_way,
			'order_link' => 'index.php?controller=AdminOrders&vieworder&token='.Tools::getAdminTokenLite('AdminOrders')
		));

		return $this->context->smarty->fetch(_DPDPOLAND_TPL_DIR_.'admin/country_list.tpl');
	}
}