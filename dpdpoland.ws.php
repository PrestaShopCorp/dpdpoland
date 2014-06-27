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

require_once dirname(__FILE__).'/dpdpoland.lang.php';

class DpdPolandWS extends DpdPolandController
{
	private		$client; // instance of SoapClient class

	private 	$params = array();

	private 	$lastCalledFunctionPayload;

	private 	$lastCalledFunctionName;

	private 	$lastCalledFunctionArgs = array();

	const 	FILENAME 				= 'dpdpoland.ws';
	const 	DEBUG_FILENAME			= 'DPDPOLAND_DEBUG_FILENAME'; // @TODO prepend config_
	const 	DEBUG_POPUP				= false;
	const 	DEBUG_FILENAME_LENGTH 	= 16; // @TODO prepend config_

    public function __construct($wsdl = null)
    {
		$settings = new DpdPolandConfiguration;

		$this->params = array(
			'authDataV1' => array(
				'login' => pSQL($settings->login),
				'masterFid' => pSQL($settings->customer_fid),
				'password' => pSQL($settings->password)
			)
		);

        try
		{
			$timeout = 0; // @TODO value has to be taken from settings
			$this->client = new SoapClient($settings->ws_url, array('connection_timeout' => (int)$timeout, 'trace' => true));
			return $this->client;
        }
        catch (Exception $e)
		{
            self::$errors[] = $e->getMessage();
        }

		return false;
    }

	public function __call($function_name, $arguments)
	{
		$result = null;

		$this->lastCalledFunctionName = $function_name;
		$this->lastCalledFunctionArgs = $arguments;

		if (isset($arguments[0]) && is_array($arguments[0]))
		{
			$this->params = array_merge($this->params, $arguments[0]);

			try
			{
				if (!$result = $this->client->$function_name($this->params))
					self::$errors[] = $this->l('Could not connect to webservice server. Please check webservice URL');
			}
			catch (Exception $e)
			{
				self::$errors[] = $e->getMessage();
			}

			if (isset($result->return))
				$result = $result->return;

			if (isset($result->faultstring))
				self::$errors[] = $result->faultstring;

			if (_DPDPOLAND_DEBUG_MODE_)
				$this->debug($result);

			return $this->objectToArray($result);
		}

		return false;
	}

	/* we want to have response as array */
	private function objectToArray($response)
	{
		if(!is_object($response) && !is_array($response))
			return $response;

		return array_map(array($this, 'objectToArray'), (array)$response);
	}

	private function createDebugFileIfNotExists()
	{
		if ((!$debug_filename = Configuration::get(self::DEBUG_FILENAME)) || !$this->isDebugFileName($debug_filename))
		{
			$debug_filename = Tools::passwdGen(self::DEBUG_FILENAME_LENGTH).'.html';
			Configuration::updateValue(self::DEBUG_FILENAME, $debug_filename);
		}

		if (!file_exists(_DPDPOLAND_MODULE_DIR_.$debug_filename))
		{
			$file = fopen(_DPDPOLAND_MODULE_DIR_.$debug_filename, 'w');
			fclose($file);
		}

		return $debug_filename;
	}

	private function isDebugFileName($debug_filename)
	{
		return Tools::strlen($debug_filename) == (int)self::DEBUG_FILENAME_LENGTH + 5 && preg_match('#^[a-zA-Z0-9]+\.html$#', $debug_filename);
	}

	private function debug($result = null)
	{
		$debug_html = '';

		if ($this->lastCalledFunctionName)
		{
			$debug_html .= '<h2 style="padding: 10px 0 10px 0; display: block; border-top: solid 2px #000000; border-bottom: solid 2px #000000;">
			['.date('Y-m-d H:i:s').']</h2><h2>Function \''.$this->lastCalledFunctionName.'\' params
			</h2><pre>';
			$debug_html .= print_r($this->lastCalledFunctionArgs, true);
			$debug_html .= '</pre>';
		}

		if ($this->lastCalledFunctionPayload = (string)$this->client->__getLastRequest())
			$debug_html .= '<h2>Request</h2><pre>' . $this->displayPayload() . '</pre>';

		if ($result)
		{
			if ($err = $this->getError())
				$debug_html .= '<h2>Error</h2><pre>' . $err . '</pre>';
			else
			{
				$debug_html .= '<h2>Response</h2><pre>';
				$debug_html .= print_r($result, true);
				$debug_html .= '</pre>';
			}
		}
		else
		{
			$debug_html .= '<h2>Errors</h2><pre>' . print_r(self::$errors, true) . '</pre>';
		}

		if ($debug_html)
		{
			$debug_filename = $this->createDebugFileIfNotExists();

			$current_content = Tools::file_get_contents(_DPDPOLAND_MODULE_DIR_.$debug_filename);
			@file_put_contents(_DPDPOLAND_MODULE_DIR_.$debug_filename, $debug_html.$current_content, LOCK_EX);

			if (self::DEBUG_POPUP)
			{
				echo '
				<div id="_invertus_ws_console" style="display:none">
					'.$debug_html.'
				</div>
				<script language=javascript>
					_invertus_ws_console = window.open("","Invertus WS debugging console","width=680,height=600,resizable,scrollbars=yes");
					_invertus_ws_console.document.write("<HTML><TITLE>Invertus WS debugging console</TITLE><BODY bgcolor=#ffffff>");
					_invertus_ws_console.document.write(document.getElementById("_invertus_ws_console").innerHTML);
					_invertus_ws_console.document.write("</BODY></HTML>");
					_invertus_ws_console.document.close();
					document.getElementById("_invertus_ws_console").remove();
				</script>';
			}
		}
	}

	/* only for debugging purposes */
	private function displayPayload()
	{
		$xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $this->lastCalledFunctionPayload);
		$token      = strtok($xml, "\n");
		$result     = '';
		$pad        = 0;
		$matches    = array();
		while ($token !== false) :
			if (preg_match('/.+<\/\w[^>]*>$/', $token, $matches)) :
			  $indent=0;
			elseif (preg_match('/^<\/\w/', $token, $matches)) :
			  $pad-=4;
			  $indent = 0;
			elseif (preg_match('/^<\w[^>]*[^\/]>.*$/', $token, $matches)) :
			  $indent = 4;
			else :
			  $indent = 0;
			endif;
			$line    = str_pad($token, Tools::strlen($token)+$pad, ' ', STR_PAD_LEFT);
			$result .= $line . "\n";
			$token   = strtok("\n");
			$pad    += $indent;
		endwhile;

		return htmlentities($result);
	}
}