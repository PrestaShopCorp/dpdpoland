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

/* URI constants */

if (!defined('_DPDPOLAND_MODULE_URI_'))
	define('_DPDPOLAND_MODULE_URI_', _MODULE_DIR_.'dpdpoland/');

if (!defined('_DPDPOLAND_LIBRARIES_DIR_'))
	define('_DPDPOLAND_LIBRARIES_DIR_', _PS_MODULE_DIR_.'dpdpoland/libraries/');

if (!defined('_DPDPOLAND_CSS_URI_'))
	define('_DPDPOLAND_CSS_URI_', _DPDPOLAND_MODULE_URI_.'css/');

if (!defined('_DPDPOLAND_JS_URI_'))
	define('_DPDPOLAND_JS_URI_', _DPDPOLAND_MODULE_URI_.'js/');

if (!defined('_DPDPOLAND_IMG_URI_'))
	define('_DPDPOLAND_IMG_URI_', _DPDPOLAND_MODULE_URI_.'img/');

if (!defined('_DPDPOLAND_AJAX_URI_'))
	define('_DPDPOLAND_AJAX_URI_', _DPDPOLAND_MODULE_URI_.'dpdpoland.ajax.php');

if (!defined('_DPDPOLAND_PDF_URI_'))
	define('_DPDPOLAND_PDF_URI_', _DPDPOLAND_MODULE_URI_.'dpdpoland.pdf.php');

/* Directories constants */

if (!defined('_DPDPOLAND_CONTROLLERS_DIR_'))
	define('_DPDPOLAND_CONTROLLERS_DIR_', dirname(__FILE__).'/controllers/');

if (!defined('_DPDPOLAND_TPL_DIR_'))
	define('_DPDPOLAND_TPL_DIR_', dirname(__FILE__).'/views/templates/');

if (!defined('_DPDPOLAND_CLASSES_DIR_'))
	define('_DPDPOLAND_CLASSES_DIR_', dirname(__FILE__).'/classes/');

if (!defined('_DPDPOLAND_MODULE_DIR_'))
	define('_DPDPOLAND_MODULE_DIR_', _PS_MODULE_DIR_.'dpdpoland/');

if (!defined('_DPDPOLAND_IMG_DIR_'))
	define('_DPDPOLAND_IMG_DIR_', _DPDPOLAND_MODULE_DIR_.'img/');

/*  */

if (!defined('_DPDPOLAND_DEBUG_MODE_'))
	define('_DPDPOLAND_DEBUG_MODE_', true);

if (!defined('_DPDPOLAND_PRICE_RULE_DB_'))
	define('_DPDPOLAND_PRICE_RULE_DB_', 'dpdpoland_price_rule');

if (!defined('_DPDPOLAND_PAYER_NUMBERS_DB_'))
	define('_DPDPOLAND_PAYER_NUMBERS_DB_', 'dpdpoland_payer_number');

if (!defined('_DPDPOLAND_COUNTRY_DB_'))
	define('_DPDPOLAND_COUNTRY_DB_', 'dpdpoland_country');

if (!defined('_DPDPOLAND_MANIFEST_DB_'))
	define('_DPDPOLAND_MANIFEST_DB_', 'dpdpoland_manifest');

if (!defined('_DPDPOLAND_PACKAGE_DB_'))
	define('_DPDPOLAND_PACKAGE_DB_', 'dpdpoland_package');

if (!defined('_DPDPOLAND_PARCEL_DB_'))
	define('_DPDPOLAND_PARCEL_DB_', 'dpdpoland_parcel');

if (!defined('_DPDPOLAND_PARCEL_PRODUCT_DB_'))
	define('_DPDPOLAND_PARCEL_PRODUCT_DB_', 'dpdpoland_parcel_product');

if (!defined('_DPDPOLAND_ORDER_COD_DB_'))
	define('_DPDPOLAND_ORDER_COD_DB_', 'dpdpoland_order_cod');

if (!defined('_DPDPOLAND_CARRIER_DB_'))
	define('_DPDPOLAND_CARRIER_DB_', 'dpdpoland_carrier');

if (!defined('_DPDPOLAND_CSV_DELIMITER_'))
	define('_DPDPOLAND_CSV_DELIMITER_', ';');

if (!defined('_DPDPOLAND_CSV_FILENAME_'))
	define('_DPDPOLAND_CSV_FILENAME_', 'dpdpoland');

if (!defined('_DPDPOLAND_STANDARD_ID_'))
	define('_DPDPOLAND_STANDARD_ID_', 1);

if (!defined('_DPDPOLAND_STANDARD_COD_ID_'))
	define('_DPDPOLAND_STANDARD_COD_ID_', 2);

if (!defined('_DPDPOLAND_CLASSIC_ID_'))
	define('_DPDPOLAND_CLASSIC_ID_', 3);

if (!defined('_DPDPOLAND_CURRENCY_ISO_'))
	define('_DPDPOLAND_CURRENCY_ISO_', 'PLN');

if (!defined('_DPDPOLAND_DEFAULT_WEIGHT_UNIT_'))
	define('_DPDPOLAND_DEFAULT_WEIGHT_UNIT_', 'kg');

if (!defined('_DPDPOLAND_DEFAULT_DIMENSION_UNIT_'))
	define('_DPDPOLAND_DEFAULT_DIMENSION_UNIT_', 'cm');

if (!defined('_DPDPOLAND_DIMENTION_WEIGHT_DIVISOR_'))
	define('_DPDPOLAND_DIMENTION_WEIGHT_DIVISOR_', 6000);

if (!defined('_DPDPOLAND_TRACKING_URL_'))
	define('_DPDPOLAND_TRACKING_URL_',
		'http://www.dpd.com.pl/tracking.asp?p1=@&przycisk.x=14&przycisk.y=6&przycisk=Wyszukaj&przycisk=Wyszukaj&ID_kat=3&ID=33&Mark=18');

if (!defined('_DPDPOLAND_PRICES_ZIP_URL_'))
	define('_DPDPOLAND_PRICES_ZIP_URL_', 'http://www.dpd.com.pl/EN/download/Cennik_uslug_krajowych_DPD_Polska_01_07_2013.zip');

if (!defined('_DPDPOLAND_REFERENCE3_'))
	define('_DPDPOLAND_REFERENCE3_', 'PSMODUL#');

if (!defined('_DPDPOLAND_COOKIE_'))
	define('_DPDPOLAND_COOKIE_', 'dpdpoland_cookie');