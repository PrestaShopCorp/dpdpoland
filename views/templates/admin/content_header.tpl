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
	<p>{l s='As leading provider of standard and express shipping services in Poland, DPD does not only operate a highly efficient transport network with over 500 depots in more than 40 countries, DPD also develops individual solutions so that you have quick access to all the world\'s major business regions.' mod='dpdpoland'}</p>
	<p>{l s='DPD offers the right solutions for every possible shipping requirement.' mod='dpdpoland'}</p>
	<div class="clear clearfix"></div>
	<ul>
			<li><b>{l s='From the domestic standard parcel,' mod='dpdpoland'}</b>&nbsp;{l s='all the way to time-definite delivery the following day, DPD will bring your shipment quickly and reliably to your customer.' mod='dpdpoland'}</li>
			<li><b>{l s='International delivery?' mod='dpdpoland'}</b>&nbsp;{l s='Trust the DPD international service. You can reach many countries around the world quickly and reliably!' mod='dpdpoland'}</li>
			<li><b>{l s='Maybe C.O.D.?' mod='dpdpoland'}</b>&nbsp;{l s='In our domestic service you can use our cash-on-delivery option. We will only deliver your parcel in return for immediate payment. We collect the payment before handing over the parcel, and send it securely to you on the receiver’s behalf.' mod='dpdpoland'}</li>
		</ul>
		<p><a href="{$smarty.const._DPDPOLAND_PRICES_ZIP_URL_|escape:'htmlall':'UTF-8'}">{l s='Check out our prices!' mod='dpdpoland'}</a></p>
		<p>{l s='To send your parcels via DPD, firstly please' mod='dpdpoland'} <a href="mailto: prestashop@dpd.com.pl">{l s='contact' mod='dpdpoland'}</a> {l s='and then configure your module.' mod='dpdpoland'}</p>
</fieldset>
{if !$ps_16}
	<br />
{/if}