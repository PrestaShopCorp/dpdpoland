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
<script>
	$(document).ready(function(){
		$("#pickupDate").datepicker({
			prevText: '',
			nextText: '',
			dateFormat: 'yy-mm-dd',
			beforeShowDay: $.datepicker.noWeekends,
			minDate: new Date()
		});
		
		if ($('input[name="dox"]').is(':checked')) {
			$('#envelopes_container').show();
		}
		
		if ($('input[name="parcels"]').is(':checked')) {
			$('#parcels_container').show();
		}
		
		if ($('input[name="pallet"]').is(':checked')) {
			$('#pallets_container').show();
		}
	});
</script>

<form id="arrange_pickup_form" class="defaultForm" method="post">
    <fieldset id="sender_address">
        <legend><img src="{$smarty.const._DPDPOLAND_IMG_URI_|escape:'htmlall':'UTF-8'}settings.png" /> {l s='Sender address' mod='dpdpoland'}</legend>
		{if $settings->address AND $settings->postcode AND $settings->city}
			{$settings->address|escape:'htmlall':'UTF-8'}, {$settings->postcode|escape:'htmlall':'UTF-8'} {$settings->city|escape:'htmlall':'UTF-8'}, {l s='Poland' mod='dpdpoland'}
		{else}
			<p class="warn warning">{l s='Sender address must be specified in settings first' mod='dpdpoland'}</p>
		{/if}
    </fieldset>
    
    <br />
    
    <fieldset id="pickup_date">
		<legend><img src="{$smarty.const._DPDPOLAND_IMG_URI_}settings.png" /> {l s='Date and time for pickup' mod='dpdpoland'}</legend>
        <label>
            {l s='Date of pickup:' mod='dpdpoland'}
        </label>
        <div class="margin-form">
            <input class="check_product_name  updateCurrentText ac_input" type="text" id="pickupDate" name="pickupDate" value="{if isset($pickupDate)}{$pickupDate|escape:'htmlall':'UTF-8'}{/if}" />
			<br /><br />
			<div class="info">
				{l s='Order placement is possible only on working days, if you select non working day your order will be realized on first available working day' mod='dpdpoland'}
			</div>
		</div>
		
        <div class="clear"></div>
        
        <label>
            {l s='Timeframe of pickup:' mod='dpdpoland'}
        </label>
        <div id="timeframe_container" class="margin-form">
			
        </div>
        <div class="clear"></div>
    </fieldset>
	
	<br />
	<fieldset id="package">
        <legend>
            <img src="{$smarty.const._DPDPOLAND_IMG_URI_|escape:'htmlall':'UTF-8'}settings.png" alt="{l s='Package' mod='dpdpoland'}" />
            {l s='Package' mod='dpdpoland'}
        </legend>
        
        <label>
            {l s='Shipment type:' mod='dpdpoland'}
        </label>
		<div class="margin-form">
			<input type="radio" checked="checked" value="DOMESTIC" autocomplete="off" name="orderType" id="shipment_type_domestic" {if isset($smarty.post.orderType) && $smarty.post.orderType == 'DOMESTIC'} checked="checked"{/if}>
			<label for="shipment_type_domestic" class="t">
				{l s='Domestic' mod='dpdpoland'}
			</label>
			<input type="radio" value="INTERNATIONAL" autocomplete="off" id="shipment_type_international" name="orderType" {if isset($smarty.post.orderType) && $smarty.post.orderType == 'INTERNATIONAL'} checked="checked"{/if}>
			<label for="shipment_type_international" class="t">
				{l s='International' mod='dpdpoland'}
			</label>
		</div>
		
        <div class="clear"></div>
        
        <div class="separation"></div>

        <label>
            {l s='Envelopes:' mod='dpdpoland'}
        </label>
        <div class="margin-form">
			<input type="hidden" name="parcels" value="0" />
            <input id="toggleEnvelope" type="checkbox" autocomplete="off" name="dox" value="1" {if isset($smarty.post.dox) && $smarty.post.dox == 1} checked="checked"{/if} />
        </div>
		<div class="clear"></div>
		
		<div id="envelopes_container" style="display:none">
			<label>
				{l s='Number of envelopes:' mod='dpdpoland'}
			</label>
			<div class="margin-form">
				<input type="text" name="doxCount" value="{if isset($smarty.post.doxCount)}{$smarty.post.doxCount|escape:'htmlall':'UTF-8'}{/if}" />
			</div>
			<div class="clear"></div>
		</div>
		
		<div class="separation"></div>
		
		{* Parcels begin *}
        <label>
            {l s='Parcels:' mod='dpdpoland'}
        </label>
        <div class="margin-form">
			<input type="hidden" name="parcels" value="0" />
            <input id="toggleParcel" type="checkbox" autocomplete="off" name="parcels" value="1" {if isset($smarty.post.parcels) && $smarty.post.parcels == 1} checked="checked"{/if} />
        </div>
		<div class="clear"></div>
		
		<div id="parcels_container" style="display:none">
			<label>
				{l s='Number of parcels:' mod='dpdpoland'}
			</label>
			<div class="margin-form">
				<input type="text" name="parcelsCount" value="{if isset($smarty.post.parcelsCount)}{$smarty.post.parcelsCount|escape:'htmlall':'UTF-8'}{/if}" />
			</div>
			<div class="clear"></div>
			
			<label>
				{l s='Summary weight (in kg):' mod='dpdpoland'}
			</label>
			<div class="margin-form">
				<input type="text" name="parcelsWeight" value="{if isset($smarty.post.parcelsWeight)}{$smarty.post.parcelsWeight|escape:'htmlall':'UTF-8'}{/if}" />
			</div>
			<div class="clear"></div>
			
			<label>
				{l s='Weight of the heaviest item (in kg):' mod='dpdpoland'}
			</label>
			<div class="margin-form">
				<input type="text" name="parcelMaxWeight" value="{if isset($smarty.post.parcelMaxWeight)}{$smarty.post.parcelMaxWeight|escape:'htmlall':'UTF-8'}{/if}" />
			</div>
			<div class="clear"></div>
			
			<label>
				{l s='Height of the tallest item (in cm):' mod='dpdpoland'}
			</label>
			<div class="margin-form">
				<input type="text" name="parcelMaxHeight" value="{if isset($smarty.post.parcelMaxHeight)}{$smarty.post.parcelMaxHeight|escape:'htmlall':'UTF-8'}{/if}" />
			</div>
			<div class="clear"></div>
			
			<label>
				{l s='Length of the largest item (in cm):' mod='dpdpoland'}
			</label>
			<div class="margin-form">
				<input type="text" name="parcelMaxDepth" value="{if isset($smarty.post.parcelMaxDepth)}{$smarty.post.parcelMaxDepth|escape:'htmlall':'UTF-8'}{/if}" />
			</div>
			<div class="clear"></div>
			
			<label>
				{l s='Width of the longest item (in cm):' mod='dpdpoland'}
			</label>
			<div class="margin-form">
				<input type="text" name="parcelMaxWidth" value="{if isset($smarty.post.parcelMaxWidth)}{$smarty.post.parcelMaxWidth|escape:'htmlall':'UTF-8'}{/if}" />
			</div>
			<div class="clear"></div>
		</div>
		{* Parcels end *}
		
		<div class="separation"></div>
		{* Pallets begin *}
		
        <label>
            {l s='Pallets:' mod='dpdpoland'}
        </label>
        <div class="margin-form">
			<input type="hidden" name="pallet" value="0" />
            <input id="togglePallet" type="checkbox" name="pallet" value="1" autocomplete="off" {if isset($smarty.post.pallet) && $smarty.post.pallet == 1} checked="checked"{/if} />
        </div>
		<div class="clear"></div>
		
		<div id="pallets_container" style="display:none">
			<label>
				{l s='Number of pallets:' mod='dpdpoland'}
			</label>
			<div class="margin-form">
				<input type="text" name="palletsCount" value="{if isset($smarty.post.palletsCount)}{$smarty.post.palletsCount|escape:'htmlall':'UTF-8'}{/if}" />
			</div>
			<div class="clear"></div>
			
			<label>
				{l s='Summary weight (in kg):' mod='dpdpoland'}
			</label>
			<div class="margin-form">
				<input type="text" name="palletsWeight" value="{if isset($smarty.post.palletsWeight)}{$smarty.post.palletsWeight|escape:'htmlall':'UTF-8'}{/if}" />
			</div>
			<div class="clear"></div>
			
			<label>
				{l s='Weight of the heaviest item (in kg):' mod='dpdpoland'}
			</label>
			<div class="margin-form">
				<input type="text" name="palletMaxWeight" value="{if isset($smarty.post.palletMaxWeight)}{$smarty.post.palletMaxWeight|escape:'htmlall':'UTF-8'}{/if}" />
			</div>
			<div class="clear"></div>
			
			<label>
				{l s='Height of the tallest item (in cm):' mod='dpdpoland'}
			</label>
			<div class="margin-form">
				<input type="text" name="palletMaxHeight" value="{if isset($smarty.post.palletMaxHeight)}{$smarty.post.palletMaxHeight|escape:'htmlall':'UTF-8'}{/if}" />
			</div>
			<div class="clear"></div>
		</div>
		{* Palets end *}
	</fieldset>
	
    <br />
    
    <fieldset id="send_pickup_request">
		<legend><img src="{$smarty.const._DPDPOLAND_IMG_URI_|escape:'htmlall':'UTF-8'}settings.png" /> {l s='Send' mod='dpdpoland'}</legend>
		<p class="info">
			{l s='Some text for merchants information - to be agreed on.' mod='dpdpoland'}
		</p>
        <div class="margin-form">
            <input type="submit" name="requestPickup" class="button" value="{l s='Arrange Pickup' mod='dpdpoland'}" />
			<input type="button" class="button" value="{l s='Cancel' mod='dpdpoland'}" onclick="window.location = '{$module_link|escape:'htmlall':'UTF-8'}'" />
        </div>
        <div class="clear"></div>
    </fieldset>
</form>