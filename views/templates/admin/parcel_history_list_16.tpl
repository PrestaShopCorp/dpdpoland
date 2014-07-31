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
		$("table.ParcelHistories .datepicker").datepicker({
			prevText: '',
			nextText: '',
			dateFormat: 'yy-mm-dd'
		});

		$('table#parcel_history_list .filter').keypress(function(event){
			formSubmit(event, 'submitFilterButtonParcelHistories');
		})
	});
</script>

<form id="parcels_history_form" class="form-horizontal clearfix" action="{$full_url|escape:'htmlall':'UTF-8'}" method="post">
	<input type="hidden" value="0" name="submitFilterButtonParcelHistories" id="submitFilterParcelHistories" />
	<div class="panel col-lg-12">
		<div class="panel-heading">
			{l s='Parcels history' mod='dpdpoland'}
			<span class="badge">{$list_total|escape:'htmlall':'UTF-8'}</span>
		</div>

		<div class="table-responsive clearfix">
			<table id="packages" class="table ParcelHistories">
				<thead>
					<tr class="nodrag nodrop">
						<th class="fixed-width-xs center">
							<span class="title_box{if $order_by == 'id_order'} active{/if}">
								{l s='Order ID' mod='dpdpoland'}
								<a{if $order_by == 'id_order' && $order_way == 'desc'} class="active"{/if} href="{$full_url|escape:'htmlall':'UTF-8'}&ParcelHistoriesOrderBy=id_order&ParcelHistoriesOrderWay=desc">
									<i class="icon-caret-down"></i>
								</a>
								<a{if $order_by == 'id_order' && $order_way == 'asc'} class="active"{/if} href="{$full_url|escape:'htmlall':'UTF-8'}&ParcelHistoriesOrderBy=id_order&ParcelHistoriesOrderWay=asc">
									<i class="icon-caret-up"></i>
								</a>
							</span>
						</th>
						<th class="left">
							<span class="title_box{if $order_by == 'id_parcel'} active{/if}">
								{l s='Parcel Number' mod='dpdpoland'}
								<a{if $order_by == 'id_parcel' && $order_way == 'desc'} class="active"{/if} href="{$full_url|escape:'htmlall':'UTF-8'}&ParcelHistoriesOrderBy=id_parcel&ParcelHistoriesOrderWay=desc">
									<i class="icon-caret-down"></i>
								</a>
								<a{if $order_by == 'id_parcel' && $order_way == 'asc'} class="active"{/if} href="{$full_url|escape:'htmlall':'UTF-8'}&ParcelHistoriesOrderBy=id_parcel&ParcelHistoriesOrderWay=asc">
									<i class="icon-caret-up"></i>
								</a>
							</span>
						</th>
						<th>
							<span class="title_box{if $order_by == 'receiver'} active{/if}">
								{l s='Receiver' mod='dpdpoland'}
								<a{if $order_by == 'receiver' && $order_way == 'desc'} class="active"{/if} href="{$full_url|escape:'htmlall':'UTF-8'}&ParcelHistoriesOrderBy=receiver&ParcelHistoriesOrderWay=desc">
									<i class="icon-caret-down"></i>
								</a>
								<a{if $order_by == 'receiver' && $order_way == 'asc'} class="active"{/if} href="{$full_url|escape:'htmlall':'UTF-8'}&ParcelHistoriesOrderBy=receiver&ParcelHistoriesOrderWay=asc">
									<i class="icon-caret-up"></i>
								</a>
							</span>
						</th>
						<th class="left">
							<span class="title_box{if $order_by == 'country'} active{/if}">
								{l s='Country' mod='dpdpoland'}
								<a{if $order_by == 'country' && $order_way == 'desc'} class="active"{/if} href="{$full_url|escape:'htmlall':'UTF-8'}&ParcelHistoriesOrderBy=country&ParcelHistoriesOrderWay=desc">
									<i class="icon-caret-down"></i>
								</a>
								<a{if $order_by == 'country' && $order_way == 'asc'} class="active"{/if} href="{$full_url|escape:'htmlall':'UTF-8'}&ParcelHistoriesOrderBy=country&ParcelHistoriesOrderWay=asc">
									<i class="icon-caret-up"></i>
								</a>
							</span>
						</th>
						<th class="fixed-width-xs center">
							<span class="title_box{if $order_by == 'postcode'} active{/if}">
								{l s='Postal code' mod='dpdpoland'}
								<a{if $order_by == 'postcode' && $order_way == 'desc'} class="active"{/if} href="{$full_url|escape:'htmlall':'UTF-8'}&ParcelHistoriesOrderBy=postcode&ParcelHistoriesOrderWay=desc">
									<i class="icon-caret-down"></i>
								</a>
								<a{if $order_by == 'postcode' && $order_way == 'asc'} class="active"{/if} href="{$full_url|escape:'htmlall':'UTF-8'}&ParcelHistoriesOrderBy=postcode&ParcelHistoriesOrderWay=asc">
									<i class="icon-caret-up"></i>
								</a>
							</span>
						</th>
						<th>
							<span class="title_box{if $order_by == 'city'} active{/if}">
								{l s='City' mod='dpdpoland'}
								<a{if $order_by == 'city' && $order_way == 'desc'} class="active"{/if} href="{$full_url|escape:'htmlall':'UTF-8'}&ParcelHistoriesOrderBy=city&ParcelHistoriesOrderWay=desc">
									<i class="icon-caret-down"></i>
								</a>
								<a{if $order_by == 'city' && $order_way == 'asc'} class="active"{/if} href="{$full_url|escape:'htmlall':'UTF-8'}&ParcelHistoriesOrderBy=city&ParcelHistoriesOrderWay=asc">
									<i class="icon-caret-up"></i>
								</a>
							</span>
						</th>
						<th class="fixed-width-lg center">
							<span class="title_box{if $order_by == 'address'} active{/if}">
								{l s='Address' mod='dpdpoland'}
								<a{if $order_by == 'address' && $order_way == 'desc'} class="active"{/if} href="{$full_url|escape:'htmlall':'UTF-8'}&ParcelHistoriesOrderBy=address&ParcelHistoriesOrderWay=desc">
									<i class="icon-caret-down"></i>
								</a>
								<a{if $order_by == 'address' && $order_way == 'asc'} class="active"{/if} href="{$full_url|escape:'htmlall':'UTF-8'}&ParcelHistoriesOrderBy=address&ParcelHistoriesOrderWay=asc">
									<i class="icon-caret-up"></i>
								</a>
							</span>
						</th>
						<th class="fixed-width-lg text-right">
							<span class="title_box{if $order_by == 'date_add'} active{/if}">
								{l s='Shipment date' mod='dpdpoland'}
								<a{if $order_by == 'date_add' && $order_way == 'desc'} class="active"{/if} href="{$full_url|escape:'htmlall':'UTF-8'}&ParcelHistoriesOrderBy=date_add&ParcelHistoriesOrderWay=desc">
									<i class="icon-caret-down"></i>
								</a>
								<a{if $order_by == 'date_add' && $order_way == 'asc'} class="active"{/if} href="{$full_url|escape:'htmlall':'UTF-8'}&ParcelHistoriesOrderBy=date_add&ParcelHistoriesOrderWay=asc">
									<i class="icon-caret-up"></i>
								</a>
							</span>
						</th>
						<th></th>
					</tr>
					<tr class="nodrag nodrop filter row_hover">
						<th class="center">
							<input class="filter" type="text" value="{if Context::getContext()->cookie->__isset('ParcelHistoriesFilter_id_order') && Context::getContext()->cookie->ParcelHistoriesFilter_id_order}{Context::getContext()->cookie->ParcelHistoriesFilter_id_order}{/if}" name="ParcelHistoriesFilter_id_order" />
						</th>
						<th>
							<input class="filter" type="text" value="{if Context::getContext()->cookie->__isset('ParcelHistoriesFilter_id_parcel') && Context::getContext()->cookie->ParcelHistoriesFilter_id_parcel}{Context::getContext()->cookie->ParcelHistoriesFilter_id_parcel}{/if}" name="ParcelHistoriesFilter_id_parcel">
						</th>
						<th>
							<input class="filter" type="text" value="{if Context::getContext()->cookie->__isset('ParcelHistoriesFilter_receiver') && Context::getContext()->cookie->ParcelHistoriesFilter_receiver}{Context::getContext()->cookie->ParcelHistoriesFilter_receiver}{/if}" name="ParcelHistoriesFilter_receiver">
						</th>
						<th>
							<input class="filter" type="text" value="{if Context::getContext()->cookie->__isset('ParcelHistoriesFilter_country') && Context::getContext()->cookie->ParcelHistoriesFilter_country}{Context::getContext()->cookie->ParcelHistoriesFilter_country}{/if}" name="ParcelHistoriesFilter_country">
						</th>
						<th>
							<input class="filter" type="text" value="{if Context::getContext()->cookie->__isset('ParcelHistoriesFilter_postcode') && Context::getContext()->cookie->ParcelHistoriesFilter_postcode}{Context::getContext()->cookie->ParcelHistoriesFilter_postcode}{/if}" name="ParcelHistoriesFilter_postcode">
						</th>
						<th>
							<input class="filter" type="text" value="{if Context::getContext()->cookie->__isset('ParcelHistoriesFilter_city') && Context::getContext()->cookie->ParcelHistoriesFilter_city}{Context::getContext()->cookie->ParcelHistoriesFilter_city}{/if}" name="ParcelHistoriesFilter_city">
						</th>
						<th>
							<input class="filter" type="text" value="{if Context::getContext()->cookie->__isset('ParcelHistoriesFilter_address') && Context::getContext()->cookie->ParcelHistoriesFilter_address}{Context::getContext()->cookie->ParcelHistoriesFilter_address}{/if}" name="ParcelHistoriesFilter_address">
						</th>
						<th class="text-right">
							<div class="date_range row">
								<div class="input-group fixed-width-md">
									<input type="text" placeholder="{l s='From' mod='dpdpoland'}" name="ParcelHistoriesFilter_date_add[0]" id="ParcelHistoriesFilter_date_add_0" class="filter datepicker">
									<span class="input-group-addon">
										<i class="icon-calendar"></i>
									</span>
								</div>
								<div class="input-group fixed-width-md">
									<input type="text" placeholder="{l s='To' mod='dpdpoland'}" name="ParcelHistoriesFilter_date_add[1]" id="ParcelHistoriesFilter_date_add_1" class="filter datepicker">
									<span class="input-group-addon">
										<i class="icon-calendar"></i>
									</span>
								</div>
							</div>
						</th>
						<th class="actions">
							<span class="pull-right">
								<button id="submitFilterButtonParcelHistories" class="btn btn-default" data-list-id="ParcelHistories" name="submitFilter" type="submit">
									<i class="icon-search"></i>
									{l s='Search' mod='dpdpoland'}
								</button>
								{if $filters_has_value}
									<button type="submit" name="submitResetParcelHistories" class="btn btn-warning">
										<i class="icon-eraser"></i> {l s='Reset'}
									</button>
								{/if}
							</span>
						</th>
					</tr>
				</thead>
				<tbody>
					{if isset($table_data) && $table_data}
						{section name=ii loop=$table_data}
							<tr id="tr__{$table_data[ii].id_order|escape:'htmlall':'UTF-8'}_0" class="odd">
								<td class="center">
									{if $table_data[ii].id_order}
										{$table_data[ii].id_order|escape:'htmlall':'UTF-8'}
									{else}
										--
									{/if}
								</td>
								<td>
									{if $table_data[ii].id_parcel}
										{$table_data[ii].id_parcel|escape:'htmlall':'UTF-8'}
									{else}
										--
									{/if}
								</td>
								<td>
									{if $table_data[ii].receiver}
										{$table_data[ii].receiver|escape:'htmlall':'UTF-8'}
									{else}
										--
									{/if}
								</td>
								<td class="center">
									{if $table_data[ii].country}
										{$table_data[ii].country|escape:'htmlall':'UTF-8'}
									{else}
										--
									{/if}
								</td>
								<td>
									{if $table_data[ii].postcode}
										{$table_data[ii].postcode|escape:'htmlall':'UTF-8'}
									{else}
										--
									{/if}
								</td>
								<td>
									{if $table_data[ii].city}
										{$table_data[ii].city|escape:'htmlall':'UTF-8'}
									{else}
										--
									{/if}
								</td>
								<td class="fixed-width-lg center">
									{if $table_data[ii].address}
										{$table_data[ii].address|escape:'htmlall':'UTF-8'}
									{else}
										--
									{/if}
								</td>
								<td class="pointer center">
									{if $table_data[ii].date_add && $table_data[ii].date_add != '0000-00-00 00:00:00'}
										{$table_data[ii].date_add|escape:'htmlall':'UTF-8'}
									{else}
										--
									{/if}
								</td>
								<td class="text-right">
									<div class="btn-group pull-right">
										<a class=" btn btn-default" title="View" href="{$tracking_link|escape:'htmlall':'UTF-8'}{$table_data[ii].id_parcel|escape:'htmlall':'UTF-8'}">
											<i class="icon-search-plus"></i>
											{l s='View' mod='dpdpoland'}
										</a>
									</div>
								</td>
							</tr>
						{/section}
					{else}
						<tr>
							<td colspan="9" class="list-empty">
								<div class="list-empty-msg">
									<i class="icon-warning-sign list-empty-icon"></i>
									{l s='No parcels history' mod='dpdpoland'}
								</div>
							</td>
						</tr>
					{/if}
				</tbody>
			</table>
		</div>
		<div class="row">
			<div class="col-lg-6">
				
			</div>
			{include file=$smarty.const._DPDPOLAND_TPL_DIR_|cat:'admin/_pagination_16.tpl' identifier='ParcelHistories'}
		</div>
	</div>
</form>