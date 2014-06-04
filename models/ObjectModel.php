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

class DpdPolandObjectModel extends ObjectModel
{
	public static $definition = array();
	private $fields = array();
	 
	protected   $tables = array();
	protected 	$table;
	protected 	$fieldsValidate = array();
 
	protected 	$fieldsRequired = array();
	protected 	$fieldsSize = array();
	
	protected 	$identifier;
	
	const TYPE_INT = 1;
	const TYPE_BOOL = 2;
	const TYPE_STRING = 3;
	const TYPE_FLOAT = 4;
	const TYPE_DATE = 5;
	const TYPE_HTML = 6;
	const TYPE_NOTHING = 7;

	public function __construct($id = null)
	{
		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			$caller_class_name = $this->getCallerClassName(); // child class name
			
			if (isset($caller_class_name::$definition))
			{
				$this->tables = array($caller_class_name::$definition['table']);
				$this->table = $caller_class_name::$definition['table'];
				$this->identifier = $caller_class_name::$definition['primary'];
				
				foreach ($caller_class_name::$definition['fields'] as $field_name => $field)
				{
					if (!in_array($field_name, array('id_shop', 'date_upd', 'date_add')))
					{
						$validateRule = (isset($field['validate'])) ? $field['validate'] : 'isAnything';
						$this->fieldsValidate[$field_name] = $validateRule;
						
						if (isset($field['required']))
							array_push($this->fieldsRequired, $field_name);
						
						if (isset($field['size']))
							$this->fieldsSize[$field_name] = $field['size'];
					}
				}
			}
		}
		
		return parent::__construct($id);
	}
   
	private function getCallerClassName()
	{
	   return get_class($this);
	}
	
	public function getFields()
	{
		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			parent::validateFields();
			
			$caller_class_name = $this->getCallerClassName(); // child class name
			$fields = array();
			
			if (isset($caller_class_name::$definition))
			{
				foreach ($caller_class_name::$definition['fields'] as $field_name => $field)
				{
					if ($field_name == $this->identifier && isset($this->$field_name))
					{
						$fields[$field_name] = $this->$field_name;
					}
					else
					{
						switch($field['type'])
						{
							case 1:
								$fields[$field_name] = (int)$this->$field_name;
								break;
							case 2:
								$fields[$field_name] = (bool)$this->$field_name;
								break;
							case 3:
							case 5:
							case 6:
								$fields[$field_name] = pSQL($this->$field_name, true);
								break;
							case 4:
								$fields[$field_name] = (float)$this->$field_name;
								break;
							case 7:
								$fields[$field_name] = $this->$field_name;
								break;
						}
					}
				}
			}
			return $fields;
		}
		else
		{
			return parent::getFields();
		}
	}
}