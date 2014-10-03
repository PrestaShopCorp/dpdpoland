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
		$("table.Manifests .datepicker").datepicker({
			prevText: '',
			nextText: '',
			dateFormat: 'yy-mm-dd'
		});

		$('table#manifests_list .filter').keypress(function(event){
			formSubmit(event, 'submitFilterButtonManifests');
		})
	});
</script>

<form class="form" action="{$full_url|escape:'htmlall':'UTF-8'}" method="post">
    <input type="hidden" value="0" name="submitFilterManifests" id="submitFilterManifests">
    <table id="manifests_list" name="list_table" class="table_grid">
        <tbody>
            <tr>
                <td style="vertical-align: bottom;">
                    <span style="float: left;">
                        {if $page > 1}
                            <input type="image" src="../img/admin/list-prev2.gif" onclick="getE('submitFilterManifests').value=1"/>&nbsp;
                            <input type="image" src="../img/admin/list-prev.gif" onclick="getE('submitFilterManifests').value={$page|escape:'htmlall':'UTF-8' - 1}"/>
                        {/if}
                        {l s='Page' mod='dpdpoland'} <b>{$page|escape:'htmlall':'UTF-8'}</b> / {$total_pages|escape:'htmlall':'UTF-8'}
                        {if $page < $total_pages}
                            <input type="image" src="../img/admin/list-next.gif" onclick="getE('submitFilterManifests').value={$page|escape:'htmlall':'UTF-8' + 1}"/>&nbsp;
                            <input type="image" src="../img/admin/list-next2.gif" onclick="getE('submitFilterManifests').value={$total_pages|escape:'htmlall':'UTF-8'}"/>
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
                        <input type="submit" class="button" value="{l s='Filter' mod='dpdpoland'}" name="submitFilterButtonManifests" id="submitFilterButtonManifests">
                        <input type="submit" class="button" value="{l s='Reset' mod='dpdpoland'}" name="submitResetManifests">
                    </span>
                    <span class="clear"></span>
                </td>
            </tr>
            <tr>
                <td>
                    <table cellspacing="0" cellpadding="0" style="width: 100%; margin-bottom:10px;" class="table Manifests">
                        <colgroup>
                            <col>
                            <col>
                            <col>
                            <col>
							<col width="30px">
                        </colgroup>
                        <thead>
                            <tr style="height: 40px" class="nodrag nodrop">
                                <th class="center">
                                    <span class="title_box">
                                        {l s='Manifest Number' mod='dpdpoland'}
                                    </span>
                                    <br>
                                    <a href="{$full_url|escape:'htmlall':'UTF-8'}&ManifestsOrderBy=id_manifest_ws&ManifestsOrderWay=desc">
                                        {if $order_by == 'id_manifest_ws' && $order_way == 'desc'}
                                            <img border="0" src="../img/admin/down_d.gif">
                                        {else}
                                            <img border="0" src="../img/admin/down.gif">
                                        {/if}
                                    </a>
                                    <a href="{$full_url|escape:'htmlall':'UTF-8'}&ManifestsOrderBy=id_manifest_ws&ManifestsOrderWay=asc">
                                        {if $order_by == 'id_manifest_ws' && $order_way == 'asc'}
                                            <img border="0" src="../img/admin/up_d.gif">
                                        {else}
                                            <img border="0" src="../img/admin/up.gif">
                                        {/if}
                                    </a>
                                </th>
                                <th class="center">
									<span class="title_box">
										{l s='Number of Parcels' mod='dpdpoland'}
									</span>
                                    <br>
                                    <a href="{$full_url|escape:'htmlall':'UTF-8'}&ManifestsOrderBy=count_parcels&ManifestsOrderWay=desc">
                                        {if $order_by == 'count_parcels' && $order_way == 'desc'}
                                            <img border="0" src="../img/admin/down_d.gif">
                                        {else}
                                            <img border="0" src="../img/admin/down.gif">
                                        {/if}
                                    </a>
                                    <a href="{$full_url|escape:'htmlall':'UTF-8'}&ManifestsOrderBy=count_parcels&ManifestsOrderWay=asc">
                                        {if $order_by == 'count_parcels' && $order_way == 'asc'}
                                            <img border="0" src="../img/admin/up_d.gif">
                                        {else}
                                            <img border="0" src="../img/admin/up.gif">
                                        {/if}
                                    </a>
                                </th>
                                <th class="center">
									<span class="title_box">
										{l s='Number of Orders' mod='dpdpoland'}
									</span>
                                    <br>
                                    <a href="{$full_url|escape:'htmlall':'UTF-8'}&ManifestsOrderBy=count_orders&ManifestsOrderWay=desc">
                                        {if $order_by == 'count_orders' && $order_way == 'desc'}
                                            <img border="0" src="../img/admin/down_d.gif">
                                        {else}
                                            <img border="0" src="../img/admin/down.gif">
                                        {/if}
                                    </a>
                                    <a href="{$full_url|escape:'htmlall':'UTF-8'}&ManifestsOrderBy=count_orders&ManifestsOrderWay=asc">
                                        {if $order_by == 'count_orders' && $order_way == 'asc'}
                                            <img border="0" src="../img/admin/up_d.gif">
                                        {else}
                                            <img border="0" src="../img/admin/up.gif">
                                        {/if}
                                    </a>
                                </th>
                                <th class="center">
									<span class="title_box">
										{l s='Date of printout' mod='dpdpoland'}
									</span>
                                    <br>
                                    <a href="{$full_url|escape:'htmlall':'UTF-8'}&ManifestsOrderBy=date_add&ManifestsOrderWay=desc">
                                        {if $order_by == 'date_add' && $order_way == 'desc'}
                                            <img border="0" src="../img/admin/down_d.gif">
                                        {else}
                                            <img border="0" src="../img/admin/down.gif">
                                        {/if}
                                    </a>
                                    <a href="{$full_url|escape:'htmlall':'UTF-8'}&ManifestsOrderBy=date_add&ManifestsOrderWay=asc">
                                        {if $order_by == 'date_add' && $order_way == 'asc'}
                                            <img border="0" src="../img/admin/up_d.gif">
                                        {else}
                                            <img border="0" src="../img/admin/up.gif">
                                        {/if}
                                    </a>
                                </th>
								<th class="center">
                                    <span class="title_box">
                                        {l s='Actions' mod='dpdpoland'}<br>&nbsp;
                                    </span>
                                    <br>
                                </th>
                            </tr>
                            <tr style="height: 35px;" class="nodrag nodrop filter row_hover">
								<td class="center">
                                    <input type="text" style="width:95%" value="{if Context::getContext()->cookie->__isset('ManifestsFilter_id_manifest_ws') && Context::getContext()->cookie->ManifestsFilter_id_manifest_ws}{Context::getContext()->cookie->ManifestsFilter_id_manifest_ws}{/if}" name="ManifestsFilter_id_manifest_ws" class="filter">
                                </td>
                                <td class="center">
                                    <input type="text" style="width:95%" value="{if Context::getContext()->cookie->__isset('ManifestsFilter_count_parcels') && Context::getContext()->cookie->ManifestsFilter_count_parcels}{Context::getContext()->cookie->ManifestsFilter_count_parcels}{/if}" name="ManifestsFilter_count_parcels" class="filter">
                                </td>
                                <td class="center">
                                    <input type="text" style="width:95%" value="{if Context::getContext()->cookie->__isset('ManifestsFilter_count_orders') && Context::getContext()->cookie->ManifestsFilter_count_orders}{Context::getContext()->cookie->ManifestsFilter_count_orders}{/if}" name="ManifestsFilter_count_orders" class="filter">
                                </td>
                                <td class="right">
                                    {l s='From' mod='dpdpoland'} <input type="text" style="width:70px" value="" name="ManifestsFilter_date_add[0]" id="ManifestsFilter_date_add_0" class="filter datepicker">
                                    <br>
                                    {l s='To' mod='dpdpoland'} <input type="text" style="width:70px" value="" name="ManifestsFilter_date_add[1]" id="ManifestsFilter_date_add_1" class="filter datepicker">
                                </td>
								<td class="center">
                                    --
                                </td>
                            </tr>
                        </thead>
                        <tbody>
                            {if isset($table_data) && $table_data}
                                {section name=ii loop=$table_data}
                                    <tr class="row_hover" id="tr_{$smarty.section.ii.index|escape:'htmlall':'UTF-8' + 1}_{$table_data[ii].id_manifest_ws|escape:'htmlall':'UTF-8'}_0">
                                        <td class="center">
                                            {if $table_data[ii].id_manifest_ws}
                                                {$table_data[ii].id_manifest_ws|escape:'htmlall':'UTF-8'}
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
                                        <td class="center">
                                            {if $table_data[ii].date_add}
                                                {$table_data[ii].date_add|escape:'htmlall':'UTF-8'}
                                            {else}
                                                --
                                            {/if}
                                        </td>
										<td style="white-space: nowrap;" class="center">
                                            <a title="{l s='Print manifest' mod='dpdpoland'}" href="{$full_url|escape:'htmlall':'UTF-8'}&printManifest&id_manifest_ws={$table_data[ii].id_manifest_ws|escape:'htmlall':'UTF-8'}">
                                                <img alt="{l s='Print manifest' mod='dpdpoland'}" src="../img/admin/pdf.gif">
                                            </a>
                                        </td>
                                    </tr>
                                {/section}
                            {else}
                                <tr>
                                    <td colspan="5" class="center">
                                        {l s='There are no manifests yet' mod='dpdpoland'}
                                    </td>
                                </tr>
                            {/if}
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</form>