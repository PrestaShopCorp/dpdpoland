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
		$("table.Countries .datepicker").datepicker({
			prevText: '',
			nextText: '',
			dateFormat: 'yy-mm-dd'
		});
		
		$('table#country_list .filter').keypress(function(event){
			formSubmit(event, 'submitFilterButtonCountries');
		})
	});
</script>

<form class="form" action="{$full_url|escape:'htmlall':'UTF-8'}" method="post">
	<input type="hidden" value="0" name="submitFilterCountries" id="submitFilterCountries">
	<table id="country_list" name="list_table" class="table_grid">
		<tbody>
			<tr>
				<td style="vertical-align: bottom;">
					<span style="float: left;">
						{if $page > 1}
							<input type="image" src="../img/admin/list-prev2.gif" onclick="getE('submitFilterCountries').value=1"/>&nbsp;
							<input type="image" src="../img/admin/list-prev.gif" onclick="getE('submitFilterCountries').value={$page|escape:'htmlall':'UTF-8' - 1}"/>
						{/if}
						{l s='Page' mod='dpdpoland'} <b>{$page|escape:'htmlall':'UTF-8'}</b> / {$total_pages|escape:'htmlall':'UTF-8'}
						{if $page < $total_pages}
							<input type="image" src="../img/admin/list-next.gif" onclick="getE('submitFilterCountries').value={$page|escape:'htmlall':'UTF-8' + 1}"/>&nbsp;
							<input type="image" src="../img/admin/list-next2.gif" onclick="getE('submitFilterCountries').value={$total_pages|escape:'htmlall':'UTF-8'}"/>
						{/if}
						| {l s='Display' mod='dpdpoland'}
						<select name="pagination" onchange="submit()">
							{foreach from=$pagination item=value}
								<option value="{$value|intval|escape:'htmlall':'UTF-8'}"{if $selected_pagination == $value} selected="selected" {elseif $selected_pagination == NULL && $value == $pagination[1]} selected="selected2"{/if}>{$value|intval|escape:'htmlall':'UTF-8'}</option>
							{/foreach}
						</select>
						/ {$list_total|escape:'htmlall':'UTF-8'} {l s='result(s)' mod='dpdpoland'}
					</span>
					<span style="float: right;">
						<input type="submit" class="button" value="{l s='Filter' mod='dpdpoland'}" name="submitFilterButtonCountries" id="submitFilterButtonCountries">
						<input type="submit" class="button" value="{l s='Reset' mod='dpdpoland'}" name="submitResetCountries">
					</span>
					<span class="clear"></span>
				</td>
			</tr>
			<tr>
				<td>
					<table cellspacing="0" cellpadding="0" style="width: 100%; margin-bottom:10px;" class="table Countries">
						<colgroup>
							<col width="10px">
							<col width="80px">
							<col>
							<col width="80px">
							<col width="70px">
						</colgroup>
						<thead>
							<tr style="height: 40px" class="nodrag nodrop">
								<th class="center">
									<input type="checkbox" onclick="checkDelBoxes(this.form, 'CountriesBox[]', this.checked)" class="noborder" name="checkme">
								</th>
								<th class="center">
									<span class="title_box">
										{l s='ID' mod='dpdpoland'}
									</span>
									<br>
									<a href="{$full_url|escape:'htmlall':'UTF-8'}&CountriesOrderBy=id_country&CountriesOrderWay=desc">
										{if $order_by == 'id_country' && $order_way == 'desc'}
											<img border="0" src="../img/admin/down_d.gif">
										{else}
											<img border="0" src="../img/admin/down.gif">
										{/if}
									</a>
									<a href="{$full_url|escape:'htmlall':'UTF-8'}&CountriesOrderBy=id_country&CountriesOrderWay=asc">
										{if $order_by == 'id_country' && $order_way == 'asc'}
											<img border="0" src="../img/admin/up_d.gif">
										{else}
											<img border="0" src="../img/admin/up.gif">
										{/if}
									</a>
								</th>
								<th class="center">
									<span class="title_box">
										{l s='Country' mod='dpdpoland'}
									</span>
									<br>
									<a href="{$full_url|escape:'htmlall':'UTF-8'}&CountriesOrderBy=name&CountriesOrderWay=desc">
										{if $order_by == 'name' && $order_way == 'desc'}
											<img border="0" src="../img/admin/down_d.gif">
										{else}
											<img border="0" src="../img/admin/down.gif">
										{/if}
									</a>
									<a href="{$full_url|escape:'htmlall':'UTF-8'}&CountriesOrderBy=name&CountriesOrderWay=asc">
										{if $order_by == 'name' && $order_way == 'asc'}
											<img border="0" src="../img/admin/up_d.gif">
										{else}
											<img border="0" src="../img/admin/up.gif">
										{/if}
									</a>
								</th>
								<th class="center">
									<span class="title_box">
										{l s='ISO code' mod='dpdpoland'}
									</span>
									<br>
									<a href="{$full_url|escape:'htmlall':'UTF-8'}&CountriesOrderBy=iso_code&CountriesOrderWay=desc">
										{if $order_by == 'iso_code' && $order_way == 'desc'}
											<img border="0" src="../img/admin/down_d.gif">
										{else}
											<img border="0" src="../img/admin/down.gif">
										{/if}
									</a>
									<a href="{$full_url|escape:'htmlall':'UTF-8'}&CountriesOrderBy=iso_code&CountriesOrderWay=asc">
										{if $order_by == 'iso_code' && $order_way == 'asc'}
											<img border="0" src="../img/admin/up_d.gif">
										{else}
											<img border="0" src="../img/admin/up.gif">
										{/if}
									</a>
								</th>
								<th class="center">
									<span class="title_box">
										{l s='Enabled' mod='dpdpoland'}
									</span>
									<br>
								</th>
							</tr>
							<tr style="height: 35px;" class="nodrag nodrop filter row_hover">
								<td class="center">
									--
								</td>
								<td class="center">
									<input type="text" style="width:95%" value="{if Context::getContext()->cookie->__isset('CountriesFilter_id_country') && Context::getContext()->cookie->CountriesFilter_id_country}{Context::getContext()->cookie->CountriesFilter_id_country}{/if}" name="CountriesFilter_id_country" class="filter">
								</td>
								<td class="center">
									<input type="text" style="width:95%" value="{if Context::getContext()->cookie->__isset('CountriesFilter_name') && Context::getContext()->cookie->CountriesFilter_name}{Context::getContext()->cookie->CountriesFilter_name}{/if}" name="CountriesFilter_name" class="filter">
								</td>
								<td class="right">
									<input type="text" style="width:95%" value="{if Context::getContext()->cookie->__isset('CountriesFilter_iso_code') && Context::getContext()->cookie->CountriesFilter_iso_code}{Context::getContext()->cookie->CountriesFilter_iso_code}{/if}" name="CountriesFilter_iso_code" class="filter">
								</td>
								<td class="center">
									<select name="CountriesFilter_enabled" onchange="$('input#submitFilterButtonCountries').click();">
										<option value="">--</option>
										<option {if Context::getContext()->cookie->__isset('CountriesFilter_enabled') && Context::getContext()->cookie->CountriesFilter_enabled == '1'}selected="selected" {/if}value="1">{l s='Yes' mod='dpdpoland'}</option>
										<option {if Context::getContext()->cookie->__isset('CountriesFilter_enabled') && Context::getContext()->cookie->CountriesFilter_enabled == '0'}selected="selected" {/if}value="0">{l s='No' mod='dpdpoland'}</option>
									</select>
								</td>
							</tr>
						</thead>
						<tbody>
							{if isset($table_data) && $table_data}
								{section name=ii loop=$table_data}
									<tr class="row_hover" id="tr_{$smarty.section.ii.index|escape:'htmlall':'UTF-8' + 1}_{$table_data[ii].id_country|escape:'htmlall':'UTF-8'}_0">
										<td class="center">
											<input type="checkbox" class="noborder" value="{$table_data[ii].id_country|escape:'htmlall':'UTF-8'}" name="CountriesBox[]"{if isset($smarty.post.CountriesBox) && in_array($table_data[ii].id_country, $smarty.post.CountriesBox)} checked="checked"{/if}>
										</td>
										<td class="center">
											{if $table_data[ii].id_country}
												{$table_data[ii].id_country|escape:'htmlall':'UTF-8'}
											{else}
												--
											{/if}
										</td>
										<td>
											{if $table_data[ii].name}
												{$table_data[ii].name|escape:'htmlall':'UTF-8'}
											{else}
												--
											{/if}
										</td>
										<td class="center">
											{if $table_data[ii].iso_code}
												{$table_data[ii].iso_code|escape:'htmlall':'UTF-8'}
											{else}
												--
											{/if}
										</td>
										<td class="center">
											{if $table_data[ii].enabled == '' || $table_data[ii].enabled == 1}
												<a href="{$full_url|escape:'htmlall':'UTF-8'}&disable_country=1&id_country={$table_data[ii].id_country|escape:'htmlall':'UTF-8'}&submitFilterCountries={$page|escape:'htmlall':'UTF-8'}&pagination={$selected_pagination|escape:'htmlall':'UTF-8'}">
													<img alt="{l s='Enabled' mod='dpdpoland'}" src="../img/admin/enabled.gif" />
												</a>
											{else}
												<a href="{$full_url|escape:'htmlall':'UTF-8'}&enable_country=1&id_country={$table_data[ii].id_country|escape:'htmlall':'UTF-8'}&submitFilterCountries={$page|escape:'htmlall':'UTF-8'}&pagination={$selected_pagination|escape:'htmlall':'UTF-8'}">
													<img alt="{l s='Disabled' mod='dpdpoland'}" src="../img/admin/disabled.gif" />
												</a>
											{/if}
										</td>
									</tr>
								{/section}
							{else}
								<tr>
									<td colspan="5" class="center">
										{l s='No countries' mod='dpdpoland'}
									</td>
								</tr>
							{/if}
						</tbody>
					</table>
					<p>
						<input class="button" type="submit" onclick="return confirm('{l s='Are you sure?' mod='dpdpoland'}');" value="{l s='Disable selection' mod='dpdpoland'}" name="disableCountries" />
						<input class="button" type="submit" onclick="return confirm('{l s='Are you sure?' mod='dpdpoland'}');" value="{l s='Enable selection' mod='dpdpoland'}" name="enableCountries" />
					</p>
				</td>
			</tr>
		</tbody>
	</table>
</form>