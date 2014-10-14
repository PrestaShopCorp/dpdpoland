{** 2014 DPD Polska Sp. z o.o.
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
 * @author JSC INVERTUS www.invertus.lt <help@invertus.lt>
 * @copyright 2014 DPD Polska Sp. z o.o.
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of DPD Polska Sp. z o.o.
 *}

<fieldset id="content_header" class="panel">
	<a target="_blank" href="{$smarty.const._DPDPOLAND_CONTENT_HEADER_URL_|escape:'htmlall':'UTF-8'}" title="{$module_display_name|escape:'htmlall':'UTF-8'}">
		<img src="{$smarty.const._DPDPOLAND_IMG_URI_|escape:'htmlall':'UTF-8'}content_header_logo.png" alt="{$module_display_name|escape:'htmlall':'UTF-8'}" />
	</a>
</fieldset>
{if !$ps_16}
	<br />
{/if}