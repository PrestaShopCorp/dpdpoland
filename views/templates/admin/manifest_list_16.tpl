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
		$("table#manifests_list .datepicker").datepicker({
			prevText: '',
			nextText: '',
			dateFormat: 'yy-mm-dd'
		});

		$('table#manifests_list .filter').keypress(function(event){
			formSubmit(event, 'submitFilterButtonManifests');
		});

		$('table#packages_list .filter').keypress(function(e){
			var key = (e.keyCode ? e.keyCode : e.which);
			if (key == 13)
			{
				e.preventDefault();
				formSubmit(event, 'submitFilterButtonManifests');
			}
		});

		$('#submitFilterButtonManifests').click(function() {
			$('#submitFilterManifests').val(1);
			$('#manifests').submit();
		});

	});
</script>

<form id="manifests" class="form-horizontal clearfix" action="{$full_url|escape:'htmlall':'UTF-8'}" method="post">
    <input type="hidden" value="0" name="submitFilterButtonManifests" id="submitFilterManifests">
	<div class="panel col-lg-12">
		<div class="panel-heading">{l s='Manifests' mod='dpdpoland'}<span class="badge">{$list_total|escape:'htmlall':'UTF-8'}</span></div>
		<div class="table-responsive clearfix">
			<table class="table" id="manifests_list" name="list_table">
				<thead>
					<tr class="nodrag nodrop">
						<th class="center">
							<span class="title_box{if $order_by == 'id_manifest'} active{/if}">{l s='Manifest Number' mod='dpdpoland'}
								<a href="{$full_url|escape:'htmlall':'UTF-8'}&ManifestsOrderBy=id_manifest&ManifestsOrderWay=desc"{if $order_by == 'id_manifest' && $order_way == 'desc'} class="active"{/if}><i class="icon-caret-down"></i></a>
								<a href="{$full_url|escape:'htmlall':'UTF-8'}&ManifestsOrderBy=id_manifest&ManifestsOrderWay=asc"{if $order_by == 'id_manifest' && $order_way == 'asc'} class="active"{/if}><i class="icon-caret-up"></i></a>
							</span>
						</th>
						<th class="">
							<span class="title_box{if $order_by == 'count_parcels'} active{/if}">{l s='Number of Parcels' mod='dpdpoland'}
								<a href="{$full_url|escape:'htmlall':'UTF-8'}&ManifestsOrderBy=count_parcels&ManifestsOrderWay=desc"{if $order_by == 'count_parcels' && $order_way == 'desc'} class="active"{/if}><i class="icon-caret-down"></i></a>
								<a href="{$full_url|escape:'htmlall':'UTF-8'}&ManifestsOrderBy=count_parcels&ManifestsOrderWay=asc"{if $order_by == 'count_parcels' && $order_way == 'asc'} class="active"{/if}><i class="icon-caret-up"></i></a>
							</span>
						</th>
						<th class="left">
							<span class="title_box{if $order_by == 'count_orders'} active{/if}">{l s='Number of Orders' mod='dpdpoland'}
								<a href="{$full_url|escape:'htmlall':'UTF-8'}&ManifestsOrderBy=count_orders&ManifestsOrderWay=desc"{if $order_by == 'count_orders' && $order_way == 'desc'} class="active"{/if}><i class="icon-caret-down"></i></a>
								<a href="{$full_url|escape:'htmlall':'UTF-8'}&ManifestsOrderBy=count_orders&ManifestsOrderWay=asc"{if $order_by == 'count_orders' && $order_way == 'asc'} class="active"{/if}><i class="icon-caret-up"></i></a>
							</span>
						</th>
						<th class="">
							<span class="title_box{if $order_by == 'date_add'} active{/if}">{l s='Date of printout' mod='dpdpoland'}
								<a href="{$full_url|escape:'htmlall':'UTF-8'}&ManifestsOrderBy=date_add&ManifestsOrderWay=desc"{if $order_by == 'date_add' && $order_way == 'desc'} class="active"{/if}><i class="icon-caret-down"></i></a>
								<a href="{$full_url|escape:'htmlall':'UTF-8'}&ManifestsOrderBy=date_add&ManifestsOrderWay=asc"{if $order_by == 'date_add' && $order_way == 'asc'} class="active"{/if}><i class="icon-caret-up"></i></a>
							</span>
						</th>
						<th></th>
					</tr>

					<tr class="nodrag nodrop filter row_hover">
						<th class="center">
							<input type="text" value="{if Context::getContext()->cookie->__isset('ManifestsFilter_id_manifest') && Context::getContext()->cookie->ManifestsFilter_id_manifest}{Context::getContext()->cookie->ManifestsFilter_id_manifest}{/if}" name="ManifestsFilter_id_manifest" class="filter">
						</th>

						<th class="center">
							<input type="text" value="{if Context::getContext()->cookie->__isset('ManifestsFilter_count_parcels') && Context::getContext()->cookie->ManifestsFilter_count_parcels}{Context::getContext()->cookie->ManifestsFilter_count_parcels}{/if}" name="ManifestsFilter_count_parcels" class="filter">
						</th>

						<th>
							<input type="text" value="{if Context::getContext()->cookie->__isset('ManifestsFilter_count_orders') && Context::getContext()->cookie->ManifestsFilter_count_orders}{Context::getContext()->cookie->ManifestsFilter_count_orders}{/if}" name="ManifestsFilter_count_orders" class="filter">
						</th>

						<th class="text-right">
							<div class="date_range row">
								<div class="input-group fixed-width-md">
									<input type="text" class="filter datepicker" id="ManifestsFilter_date_add_0" name="ManifestsFilter_date_add[0]" placeholder="{l s='From' mod='dpdpoland'}">
									<span class="input-group-addon">
										<i class="icon-calendar"></i>
									</span>
								</div>
								<div class="input-group fixed-width-md">
									<input type="text" class="filter datepicker" id="ManifestsFilter_date_add_1" name="ManifestsFilter_date_add[1]" placeholder="{l s='To' mod='dpdpoland'}">
									<span class="input-group-addon">
										<i class="icon-calendar"></i>
									</span>
								</div>
							</div>
						</th>

						<th class="actions text-center">
							<span class="pull-right">
								<button id="submitFilterButtonManifests" class="btn btn-default" data-list-id="manifests_list" name="submitFilter" type="submit">
									<i class="icon-search"></i>
									{l s='Search' mod='dpdpoland'}
								</button>
								{if $filters_has_value}
									<button type="submit" name="submitResetManifests" class="btn btn-warning">
										<i class="icon-eraser"></i> {l s='Reset' mod='dpdpoland'}
									</button>
								{/if}
							</span>
						</th>
					</tr>
				</thead>

				<tbody>
				{if isset($table_data) && $table_data}
					{section name=ii loop=$table_data}
					<tr class="odd" id="tr_{$smarty.section.ii.index|escape:'htmlall':'UTF-8' + 1}_{$table_data[ii].id_manifest|escape:'htmlall':'UTF-8'}_0">
						<td class="center">
							{if $table_data[ii].id_manifest}
								{$table_data[ii].id_manifest|escape:'htmlall':'UTF-8'}
							{else}
								--
							{/if}
						</td>
						<td class="center">
							{if $table_data[ii].count_parcels}
								{$table_data[ii].count_parcels|escape:'htmlall':'UTF-8'}
							{else}
								--
							{/if}
						</td>
						<td class="center">
							{if $table_data[ii].count_orders}
								{$table_data[ii].count_orders|escape:'htmlall':'UTF-8'}
							{else}
								--
							{/if}
						</td>
						<td class="text-left">
							{if $table_data[ii].date_add}
								{$table_data[ii].date_add|escape:'htmlall':'UTF-8'}
							{else}
								--
							{/if}
						</td>
						<td style="white-space: nowrap;" class="text-right">
							<a title="{l s='Print manifest' mod='dpdpoland'}" href="{$full_url|escape:'htmlall':'UTF-8'}&printManifest&id_manifest={$table_data[ii].id_manifest|escape:'htmlall':'UTF-8'}">
								<img alt="{l s='Print manifest' mod='dpdpoland'}" src="../img/admin/pdf.gif">
							</a>
						</td>
					</tr>
					{/section}
				{else}
					<tr>
						<td colspan="9" class="list-empty">
							<div class="list-empty-msg">
								<i class="icon-warning-sign list-empty-icon"></i>
								{l s='No records found' mod='dpdpoland'}
							</div>
						</td>
					</tr>
				{/if}
				</tbody>
			</table>
			{include file=$smarty.const._DPDPOLAND_TPL_DIR_|cat:'admin/_pagination_16.tpl' identifier='Manifests'}
		</div>
	</div>
</form>