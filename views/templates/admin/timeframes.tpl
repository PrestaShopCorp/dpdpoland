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
{if $timeFrames === false}
	<p class="warn warning">{DpdPolandPickup::$errors|reset}</p>
{else}
	<select name="pickupTime">
		{if isset($extra_timeframe)}
			<option value="{$extra_timeframe|escape:'htmlall':'UTF-8'}"{if isset($smarty.post.pickupTime) && $smarty.post.pickupTime == $extra_timeframe} selected="selected"{/if}>{$extra_timeframe|escape:'htmlall':'UTF-8'}</option>
		{/if}
		{foreach from=$timeFrames item=timeFrame}
			{if isset($timeFrame.range)}
			<option value="{$timeFrame.range|escape:'htmlall':'UTF-8'}"{if isset($smarty.post.pickupTime) && $smarty.post.pickupTime == $timeFrame.range} selected="selected"{/if}>{$timeFrame.range|escape:'htmlall':'UTF-8'}</option>
			{/if}
		{/foreach}
	</select>
	<p class="preference_description">{l s='You have' mod='dpdpoland'} <span class="time_left"></span> {l s='minutes to make PickUp order for selected time frame' mod='dpdpoland'}</p>
{/if}