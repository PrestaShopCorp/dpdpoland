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

<script type="text/javascript">
	$(function() {
		$('table.country_list .filter').keypress(function(e){
			var key = (e.keyCode ? e.keyCode : e.which);
			if (key == 13)
			{
				e.preventDefault();
				formSubmit(event, 'submitFilterButtonCountries');
			}
		})
		$('#submitFilterButtonCountries').click(function() {
			$('#submitFilterCountries').val(1);
		});
	});
</script>

<form class="form-horizontal clearfix" action="{$full_url|escape:'htmlall':'UTF-8'}" method="post">
	<input type="hidden" value="0" name="submitFilterButtonCountries" id="submitFilterCountries" />
	<div class="panel col-lg-12">
		<div class="panel-heading">
			{l s='Shipment countries' mod='dpdpoland'}
			<span class="badge">{$list_total|escape:'htmlall':'UTF-8'}</span>
		</div>
		
		<div class="table-responsive clearfix">
			<table id="country_list" class="table country_list">
				<thead>
					<tr class="nodrag nodrop">
						<th class="center fixed-width-xs">
							&nbsp;
						</th>
						<th class="fixed-width-xs center">
							<span class="title_box{if $order_by == 'id_country'} active{/if}">
								{l s='ID' mod='dpdpoland'}
								<a{if $order_by == 'id_country' && $order_way == 'desc'} class="active"{/if} href="{$full_url|escape:'htmlall':'UTF-8'}&CountriesOrderBy=id_country&CountriesOrderWay=desc">
									<i class="icon-caret-down"></i>
								</a>
								<a{if $order_by == 'id_country' && $order_way == 'asc'} class="active"{/if} href="{$full_url|escape:'htmlall':'UTF-8'}&CountriesOrderBy=id_country&CountriesOrderWay=asc">
									<i class="icon-caret-up"></i>
								</a>
							</span>
						</th>
						<th class="left">
							<span class="title_box{if $order_by == 'name'} active{/if}">
								{l s='Country' mod='dpdpoland'}
								<a{if $order_by == 'name' && $order_way == 'desc'} class="active"{/if} href="{$full_url|escape:'htmlall':'UTF-8'}&CountriesOrderBy=name&CountriesOrderWay=desc">
									<i class="icon-caret-down"></i>
								</a>
								<a{if $order_by == 'name' && $order_way == 'asc'} class="active"{/if} href="{$full_url|escape:'htmlall':'UTF-8'}&CountriesOrderBy=name&CountriesOrderWay=asc">
									<i class="icon-caret-up"></i>
								</a>
							</span>
						</th>
						<th class="fixed-width-xs center">
							<span class="title_box{if $order_by == 'iso_code'} active{/if}">
								{l s='ISO code' mod='dpdpoland'}
								<a{if $order_by == 'iso_code' && $order_way == 'desc'} class="active"{/if} href="{$full_url|escape:'htmlall':'UTF-8'}&CountriesOrderBy=iso_code&CountriesOrderWay=desc">
									<i class="icon-caret-down"></i>
								</a>
								<a{if $order_by == 'iso_code' && $order_way == 'asc'} class="active"{/if} href="{$full_url|escape:'htmlall':'UTF-8'}&CountriesOrderBy=iso_code&CountriesOrderWay=asc">
									<i class="icon-caret-up"></i>
								</a>
							</span>
						</th>
						<th class="fixed-width-sm text-center">
							<span class="title_box">
								{l s='Enabled' mod='dpdpoland'}
							</span>
						</th>
						<th class="fixed-width-sm">
							&nbsp;
						</th>
					</tr>
					<tr class="nodrag nodrop filter row_hover">
						<th class="text-center">
							--
						</th>
						<th class="center">
							<input class="filter" type="text" value="{if Context::getContext()->cookie->__isset('CountriesFilter_id_country') && Context::getContext()->cookie->CountriesFilter_id_country}{Context::getContext()->cookie->CountriesFilter_id_country}{/if}" name="CountriesFilter_id_country" />
						</th>
						<th>
							<input class="filter" type="text" value="{if Context::getContext()->cookie->__isset('CountriesFilter_name') && Context::getContext()->cookie->CountriesFilter_name}{Context::getContext()->cookie->CountriesFilter_name}{/if}" name="CountriesFilter_name">
						</th>
						<th>
							<input class="filter" type="text" value="{if Context::getContext()->cookie->__isset('CountriesFilter_iso_code') && Context::getContext()->cookie->CountriesFilter_iso_code}{Context::getContext()->cookie->CountriesFilter_iso_code}{/if}" name="CountriesFilter_iso_code">
						</th>
						<th class="text-center">
							<select class="filter" name="CountriesFilter_enabled">
								<option value="">-</option>
								<option {if Context::getContext()->cookie->__isset('CountriesFilter_enabled') && Context::getContext()->cookie->CountriesFilter_enabled == '1'}selected="selected" {/if}value="1">{l s='Yes' mod='dpdpoland'}</option>
								<option {if Context::getContext()->cookie->__isset('CountriesFilter_enabled') && Context::getContext()->cookie->CountriesFilter_enabled == '0'}selected="selected" {/if}value="0">{l s='No' mod='dpdpoland'}</option>
							</select>
						</th>
						<th class="actions">
							<span class="pull-right">
								<button id="submitFilterButtonCountries" class="btn btn-default" data-list-id="Countries" name="submitFilter" type="submit">
									<i class="icon-search"></i>
									{l s='Search' mod='dpdpoland'}
								</button>
								{if isset($filters_has_value) && $filters_has_value}
									<button type="submit" name="submitResetCountries" class="btn btn-warning">
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
							<tr id="tr__{$table_data[ii].id_country|escape:'htmlall':'UTF-8'}_0" class="odd">
								<td class="text-center">
									<input class="noborder" type="checkbox" value="{$table_data[ii].id_country|escape:'htmlall':'UTF-8'}" name="CountriesBox[]"{if isset($smarty.post.CountriesBox) && in_array($table_data[ii].id_country, $smarty.post.CountriesBox)} checked="checked"{/if} />
								</td>
								<td class="pointer fixed-width-xs center">
									{if $table_data[ii].id_country}
										{$table_data[ii].id_country|escape:'htmlall':'UTF-8'}
									{else}
										--
									{/if}
								</td>
								<td class="pointer fixed-width-xs">
									{if $table_data[ii].name}
										{$table_data[ii].name|escape:'htmlall':'UTF-8'}
									{else}
										--
									{/if}
								</td>
								<td class="pointer fixed-width-xs center">
									{if $table_data[ii].iso_code}
										{$table_data[ii].iso_code|escape:'htmlall':'UTF-8'}
									{else}
										--
									{/if}
								</td>
								<td class="pointer fixed-width-sm text-center">
									{if $table_data[ii].enabled == '' || $table_data[ii].enabled == 1}
										<a class="list-action-enable action-enabled" title="{l s='Enabled' mod='dpdpoland'}" href="{$full_url|escape:'htmlall':'UTF-8'}&disable_country=1&id_country={$table_data[ii].id_country|escape:'htmlall':'UTF-8'}&submitFilterCountries={$page|escape:'htmlall':'UTF-8'}&pagination={$selected_pagination|escape:'htmlall':'UTF-8'}">
											<i class="icon-check"></i>
										</a>
									{else}
										<a class="list-action-enable action-disabled" title="{l s='Disabled' mod='dpdpoland'}" href="{$full_url|escape:'htmlall':'UTF-8'}&enable_country=1&id_country={$table_data[ii].id_country|escape:'htmlall':'UTF-8'}&submitFilterCountries={$page|escape:'htmlall':'UTF-8'}&pagination={$selected_pagination|escape:'htmlall':'UTF-8'}">
											<i class="icon-remove"></i>
										</a>
									{/if}
								</td>
								<td>
									
								</td>
							</tr>
						{/section}
					{else}
						<tr>
							<td class="list-empty" colspan="6">
								<div class="list-empty-msg">
									<i class="icon-warning-sign list-empty-icon"></i>
									{l s='No countries' mod='dpdpoland'}
								</div>
							</td>
						</tr>
					{/if}
				</tbody>
			</table>
		</div>
		<div class="row">
			<div class="col-lg-6">
				<div class="btn-group bulk-actions">
					<button class="btn btn-default dropdown-toggle" data-toggle="dropdown" type="button">
						{l s='Bulk actions' mod='dpdpoland'}
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu">
						<li>
							<a onclick="javascript:checkDelBoxes($(this).closest('form').get(0), 'CountriesBox[]', true);return false;" href="#">
								<i class="icon-check-sign"></i>
								{l s='Select all' mod='dpdpoland'}
							</a>
						</li>
						<li>
							<a onclick="javascript:checkDelBoxes($(this).closest('form').get(0), 'CountriesBox[]', false);return false;" href="#">
								<i class="icon-check-empty"></i>
								{l s='Unselect all' mod='dpdpoland'}
							</a>
						</li>
						<li class="divider"></li>
						<li>
							<a onclick="sendBulkAction($(this).closest('form').get(0), 'enableCountries');" href="#">
								<i class="icon-power-off text-success"></i>
								{l s='Enable selection' mod='dpdpoland'}
							</a>
						</li>
						<li>
							<a onclick="sendBulkAction($(this).closest('form').get(0), 'disableCountries');" href="#">
								<i class="icon-power-off text-danger"></i>
								{l s='Disable selection' mod='dpdpoland'}
							</a>
						</li>
					</ul>
				</div>
			</div>
			{if $list_total > 10}
				<div class="col-lg-4">
					<span class="pagination">
						{l s='Display'}: 
						<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
							{$selected_pagination}
							<i class="icon-caret-down"></i>
						</button>
						<ul class="dropdown-menu">
						{foreach $pagination AS $value}
							<li>
								<a href="javascript:void(0);" class="pagination-items-page" data-items="{$value|intval}">{$value}</a>
							</li>
						{/foreach}
						</ul>
						/ {$list_total} {l s='result(s)'}
						<input type="hidden" id="pagination-items-page" name="pagination" value="{$selected_pagination|intval}" />
					</span>
					<script type="text/javascript">
						$('.pagination-items-page').on('click',function(e){
							e.preventDefault();
							$('#pagination-items-page').val($(this).data("items")).closest("form").submit();
						});
					</script>
					<ul class="pagination pull-right">
						<li {if $page <= 1}class="disabled"{/if}>
							<a href="javascript:void(0);" class="pagination-link" data-page="1">
								<i class="icon-double-angle-left"></i>
							</a>
						</li>
						<li {if $page <= 1}class="disabled"{/if}>
							<a href="javascript:void(0);" class="pagination-link" data-page="{$page - 1}">
								<i class="icon-angle-left"></i>
							</a>
						</li>
						{assign p 0}
						{while $p++ < $total_pages}
							{if $p < $page-2}
								<li class="disabled">
									<a href="javascript:void(0);">&hellip;</a>
								</li>
								{assign p $page-3}
							{else if $p > $page+2}
								<li class="disabled">
									<a href="javascript:void(0);">&hellip;</a>
								</li>
								{assign p $total_pages}
							{else}
								<li {if $p == $page}class="active"{/if}>
									<a href="javascript:void(0);" class="pagination-link" data-page="{$p}">{$p}</a>
								</li>
							{/if}
						{/while}
						<li {if $page > $total_pages}class="disabled"{/if}>
							<a href="javascript:void(0);" class="pagination-link" data-page="{$page + 1}">
								<i class="icon-angle-right"></i>
							</a>
						</li>
						<li {if $page > $total_pages}class="disabled"{/if}>
							<a href="javascript:void(0);" class="pagination-link" data-page="{$total_pages}">
								<i class="icon-double-angle-right"></i>
							</a>
						</li>
					</ul>
					<script type="text/javascript">
						$('.pagination-link').on('click',function(e){
							e.preventDefault();
							$('#submitFilterCountries').val($(this).data("page")).closest("form").submit();
						});
					</script>
				</div>
			{/if}
		</div>
	</div>
</form>