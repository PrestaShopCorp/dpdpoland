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
	var dpdpoland_packages_ids = "{Context::getContext()->cookie->dpdpoland_packages_ids|escape:'htmlall':'UTF-8'}";
	
	$(document).ready(function(){
		$("table.Packages .datepicker").datepicker({
			prevText: '',
			nextText: '',
			dateFormat: 'yy-mm-dd'
		});
		
		$('table#packages_list .filter').keypress(function(event){
			formSubmit(event, 'submitFilterButtonPackages');
		})
		
		if (dpdpoland_packages_ids)
		{
			window.location = window.location + '&printManifest';
		}
	});
</script>

<form class="form" action="{$full_url|escape:'htmlall':'UTF-8'}" method="post">
    <input type="hidden" value="0" name="submitFilterPackages" id="submitFilterPackages">
    <table id="packages_list" name="list_table" class="table_grid">
        <tbody>
            <tr>
                <td style="vertical-align: bottom;">
                    <span style="float: left;">
                        {if $page > 1}
                            <input type="image" src="../img/admin/list-prev2.gif" onclick="getE('submitFilterPackages').value=1"/>&nbsp;
                            <input type="image" src="../img/admin/list-prev.gif" onclick="getE('submitFilterPackages').value={$page|escape:'htmlall':'UTF-8' - 1}"/>
                        {/if}
                        {l s='Page' mod='dpdpoland'} <b>{$page|escape:'htmlall':'UTF-8'}</b> / {$total_pages|escape:'htmlall':'UTF-8'}
                        {if $page < $total_pages}
                            <input type="image" src="../img/admin/list-next.gif" onclick="getE('submitFilterPackages').value={$page|escape:'htmlall':'UTF-8' + 1}"/>&nbsp;
                            <input type="image" src="../img/admin/list-next2.gif" onclick="getE('submitFilterPackages').value={$total_pages|escape:'htmlall':'UTF-8'}"/>
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
                        <input type="submit" class="button" value="{l s='Filter' mod='dpdpoland'}" name="submitFilterButtonPackages" id="submitFilterButtonPackages">
                        <input type="submit" class="button" value="{l s='Reset' mod='dpdpoland'}" name="submitResetPackages">
                    </span>
                    <span class="clear"></span>
                </td>
            </tr>
            <tr>
                <td>
                    <table cellspacing="0" cellpadding="0" style="width: 100%; margin-bottom:10px;" class="table Packages">
                        <colgroup>
                            <col width="10px">
                            <col width="140px">
                            <col width="100px">
							<col width="100px">
                            <col width="100px">
                            <col width="140px">
							<col width="140px">
                            <col width="80px">
                            <col width="100px">
                            <col>
                            <col width="30px">
                        </colgroup>
                        <thead>
                            <tr style="height: 40px" class="nodrag nodrop">
                                <th class="center">
                                    <input type="checkbox" onclick="checkDelBoxes(this.form, 'PackagesBox[]', this.checked)" class="noborder" name="checkme">
                                </th>
                                <th class="center">
                                    <span class="title_box">
                                        {l s='Printout date' mod='dpdpoland'}
                                    </span>
                                    <br>
                                    <a href="{$full_url|escape:'htmlall':'UTF-8'}&PackagesOrderBy=date_add&PackagesOrderWay=desc">
                                        {if $order_by == 'date_add' && $order_way == 'desc'}
                                            <img border="0" src="../img/admin/down_d.gif">
                                        {else}
                                            <img border="0" src="../img/admin/down.gif">
                                        {/if}
                                    </a>
                                    <a href="{$full_url|escape:'htmlall':'UTF-8'}&PackagesOrderBy=date_add&PackagesOrderWay=asc">
                                        {if $order_by == 'date_add' && $order_way == 'asc'}
                                            <img border="0" src="../img/admin/up_d.gif">
                                        {else}
                                            <img border="0" src="../img/admin/up.gif">
                                        {/if}
                                    </a>
                                </th>
                                <th class="center">
									<span class="title_box">
										{l s='Order number' mod='dpdpoland'}
									</span>
                                    <br>
                                    <a href="{$full_url|escape:'htmlall':'UTF-8'}&PackagesOrderBy=id_order&PackagesOrderWay=desc">
                                        {if $order_by == 'id_order' && $order_way == 'desc'}
                                            <img border="0" src="../img/admin/down_d.gif">
                                        {else}
                                            <img border="0" src="../img/admin/down.gif">
                                        {/if}
                                    </a>
                                    <a href="{$full_url|escape:'htmlall':'UTF-8'}&PackagesOrderBy=id_order&PackagesOrderWay=asc">
                                        {if $order_by == 'id_order' && $order_way == 'asc'}
                                            <img border="0" src="../img/admin/up_d.gif">
                                        {else}
                                            <img border="0" src="../img/admin/up.gif">
                                        {/if}
                                    </a>
                                </th>
								<th class="center">
									<span class="title_box">
										{l s='Package number' mod='dpdpoland'}
									</span>
                                    <br>
                                    <a href="{$full_url|escape:'htmlall':'UTF-8'}&PackagesOrderBy=package_number&PackagesOrderWay=desc">
                                        {if $order_by == 'package_number' && $order_way == 'desc'}
                                            <img border="0" src="../img/admin/down_d.gif">
                                        {else}
                                            <img border="0" src="../img/admin/down.gif">
                                        {/if}
                                    </a>
                                    <a href="{$full_url|escape:'htmlall':'UTF-8'}&PackagesOrderBy=package_number&PackagesOrderWay=asc">
                                        {if $order_by == 'package_number' && $order_way == 'asc'}
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
                                    <a href="{$full_url|escape:'htmlall':'UTF-8'}&PackagesOrderBy=count_parcel&PackagesOrderWay=desc">
                                        {if $order_by == 'count_parcel' && $order_way == 'desc'}
                                            <img border="0" src="../img/admin/down_d.gif">
                                        {else}
                                            <img border="0" src="../img/admin/down.gif">
                                        {/if}
                                    </a>
                                    <a href="{$full_url|escape:'htmlall':'UTF-8'}&PackagesOrderBy=count_parcel&PackagesOrderWay=asc">
                                        {if $order_by == 'count_parcel' && $order_way == 'asc'}
                                            <img border="0" src="../img/admin/up_d.gif">
                                        {else}
                                            <img border="0" src="../img/admin/up.gif">
                                        {/if}
                                    </a>
                                </th>
                                <th class="center">
									<span class="title_box">
										{l s='Receiver' mod='dpdpoland'}
									</span>
                                    <br>
                                    <a href="{$full_url|escape:'htmlall':'UTF-8'}&PackagesOrderBy=receiver&PackagesOrderWay=desc">
                                        {if $order_by == 'receiver' && $order_way == 'desc'}
                                            <img border="0" src="../img/admin/down_d.gif">
                                        {else}
                                            <img border="0" src="../img/admin/down.gif">
                                        {/if}
                                    </a>
                                    <a href="{$full_url|escape:'htmlall':'UTF-8'}&PackagesOrderBy=receiver&PackagesOrderWay=asc">
                                        {if $order_by == 'receiver' && $order_way == 'asc'}
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
                                    <a href="{$full_url|escape:'htmlall':'UTF-8'}&PackagesOrderBy=country&PackagesOrderWay=desc">
                                        {if $order_by == 'country' && $order_way == 'desc'}
                                            <img border="0" src="../img/admin/down_d.gif">
                                        {else}
                                            <img border="0" src="../img/admin/down.gif">
                                        {/if}
                                    </a>
                                    <a href="{$full_url|escape:'htmlall':'UTF-8'}&PackagesOrderBy=country&PackagesOrderWay=asc">
                                        {if $order_by == 'country' && $order_way == 'asc'}
                                            <img border="0" src="../img/admin/up_d.gif">
                                        {else}
                                            <img border="0" src="../img/admin/up.gif">
                                        {/if}
                                    </a>
                                </th>
                                <th class="center">
									<span class="title_box">
										{l s='Postal code' mod='dpdpoland'}
									</span>
                                    <br>
                                    <a href="{$full_url|escape:'htmlall':'UTF-8'}&PackagesOrderBy=postcode&PackagesOrderWay=desc">
                                        {if $order_by == 'postcode' && $order_way == 'desc'}
                                            <img border="0" src="../img/admin/down_d.gif">
                                        {else}
                                            <img border="0" src="../img/admin/down.gif">
                                        {/if}
                                    </a>
                                    <a href="{$full_url|escape:'htmlall':'UTF-8'}&PackagesOrderBy=postcode&PackagesOrderWay=asc">
                                        {if $order_by == 'postcode' && $order_way == 'asc'}
                                            <img border="0" src="../img/admin/up_d.gif">
                                        {else}
                                            <img border="0" src="../img/admin/up.gif">
                                        {/if}
                                    </a>
                                </th>
                                <th class="center">
									<span class="title_box">
										{l s='City' mod='dpdpoland'}
									</span>
                                    <br>
                                    <a href="{$full_url|escape:'htmlall':'UTF-8'}&PackagesOrderBy=city&PackagesOrderWay=desc">
                                        {if $order_by == 'city' && $order_way == 'desc'}
                                            <img border="0" src="../img/admin/down_d.gif">
                                        {else}
                                            <img border="0" src="../img/admin/down.gif">
                                        {/if}
                                    </a>
                                    <a href="{$full_url|escape:'htmlall':'UTF-8'}&PackagesOrderBy=city&PackagesOrderWay=asc">
                                        {if $order_by == 'city' && $order_way == 'asc'}
                                            <img border="0" src="../img/admin/up_d.gif">
                                        {else}
                                            <img border="0" src="../img/admin/up.gif">
                                        {/if}
                                    </a>
                                </th>
                                <th class="center">
									<span class="title_box">
										{l s='Address' mod='dpdpoland'}
									</span>
                                    <br>
                                    <a href="{$full_url|escape:'htmlall':'UTF-8'}&PackagesOrderBy=address&PackagesOrderWay=desc">
                                        {if $order_by == 'address' && $order_way == 'desc'}
                                            <img border="0" src="../img/admin/down_d.gif">
                                        {else}
                                            <img border="0" src="../img/admin/down.gif">
                                        {/if}
                                    </a>
                                    <a href="{$full_url|escape:'htmlall':'UTF-8'}&PackagesOrderBy=address&PackagesOrderWay=asc">
                                        {if $order_by == 'address' && $order_way == 'asc'}
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
                                    --
                                </td>
								<td class="right">
                                    {l s='From' mod='dpdpoland'} <input type="text" style="width:70px" value="" name="PackagesFilter_date_add[0]" id="PackagesFilter_date_add_0" class="filter datepicker">
                                    <br>
                                    {l s='To' mod='dpdpoland'} <input type="text" style="width:70px" value="" name="PackagesFilter_date_add[1]" id="PackagesFilter_date_add_1" class="filter datepicker">
                                </td>
                                <td class="center">
                                    <input type="text" style="width:95%" value="{if Context::getContext()->cookie->__isset('PackagesFilter_id_order') && Context::getContext()->cookie->PackagesFilter_id_order}{Context::getContext()->cookie->PackagesFilter_id_order}{/if}" name="PackagesFilter_id_order" class="filter">
                                </td>
								<td class="center">
                                    <input type="text" style="width:95%" value="{if Context::getContext()->cookie->__isset('PackagesFilter_package_number') && Context::getContext()->cookie->PackagesFilter_package_number}{Context::getContext()->cookie->PackagesFilter_package_number}{/if}" name="PackagesFilter_package_number" class="filter">
                                </td>
                                <td class="center">
                                    <input type="text" style="width:95%" value="{if Context::getContext()->cookie->__isset('PackagesFilter_count_parcel') && Context::getContext()->cookie->PackagesFilter_count_parcel}{Context::getContext()->cookie->PackagesFilter_count_parcel}{/if}" name="PackagesFilter_count_parcel" class="filter">
                                </td>
                                <td class="right">
                                    <input type="text" style="width:95%" value="{if Context::getContext()->cookie->__isset('PackagesFilter_receiver') && Context::getContext()->cookie->PackagesFilter_receiver}{Context::getContext()->cookie->PackagesFilter_receiver}{/if}" name="PackagesFilter_receiver" class="filter">
                                </td>
								<td class="right">
                                    <input type="text" style="width:95%" value="{if Context::getContext()->cookie->__isset('PackagesFilter_country') && Context::getContext()->cookie->PackagesFilter_country}{Context::getContext()->cookie->PackagesFilter_country}{/if}" name="PackagesFilter_country" class="filter">
                                </td>
                                <td class="right">
                                    <input type="text" style="width:95%" value="{if Context::getContext()->cookie->__isset('PackagesFilter_postcode') && Context::getContext()->cookie->PackagesFilter_postcode}{Context::getContext()->cookie->PackagesFilter_postcode}{/if}" name="PackagesFilter_postcode" class="filter">
                                </td>
                                <td class="right">
                                    <input type="text" style="width:95%" value="{if Context::getContext()->cookie->__isset('PackagesFilter_city') && Context::getContext()->cookie->PackagesFilter_city}{Context::getContext()->cookie->PackagesFilter_city}{/if}" name="PackagesFilter_city" class="filter">
                                </td>
                                <td class="right">
                                    <input type="text" style="width:95%" value="{if Context::getContext()->cookie->__isset('PackagesFilter_address') && Context::getContext()->cookie->PackagesFilter_address}{Context::getContext()->cookie->PackagesFilter_address}{/if}" name="PackagesFilter_address" class="filter">
                                </td>
                                <td class="center">
                                    --
                                </td>
                            </tr>
                        </thead>
                        <tbody>
                            {if isset($table_data) && $table_data}
                                {section name=ii loop=$table_data}
                                    <tr class="row_hover" id="tr_{$smarty.section.ii.index|escape:'htmlall':'UTF-8' + 1}_{$table_data[ii].id_package|escape:'htmlall':'UTF-8'}_0">
                                        <td class="center">
                                            <input type="checkbox" class="noborder" value="{$table_data[ii].id_package|escape:'htmlall':'UTF-8'}" name="PackagesBox[]"{if isset($smarty.post.PackagesBox) && in_array($table_data[ii].id_package, $smarty.post.PackagesBox)} checked="checked"{/if}>
                                        </td>
                                        <td class="center">
                                            {if $table_data[ii].date_add && $table_data[ii].date_add != '0000-00-00 00:00:00'}
                                                {$table_data[ii].date_add|escape:'htmlall':'UTF-8'}
                                            {else}
                                                --
                                            {/if}
                                        </td>
                                        <td class="center">
                                            {if $table_data[ii].id_order}
                                                {$table_data[ii].id_order|escape:'htmlall':'UTF-8'}
                                            {else}
                                                --
                                            {/if}
                                        </td>
										<td class="center">
                                            {if $table_data[ii].package_number}
                                                {$table_data[ii].package_number|escape:'htmlall':'UTF-8'}
                                            {else}
                                                --
                                            {/if}
                                        </td>
                                        <td class="center">
                                            {if $table_data[ii].count_parcel}
                                                {$table_data[ii].count_parcel|escape:'htmlall':'UTF-8'}
                                            {else}
                                                --
                                            {/if}
                                        </td>
                                        <td class="center">
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
                                        <td class="center">
                                            {if $table_data[ii].postcode}
                                                {$table_data[ii].postcode|escape:'htmlall':'UTF-8'}
                                            {else}
                                                --
                                            {/if}
                                        </td>
                                        <td class="center">
                                            {if $table_data[ii].city}
                                                {$table_data[ii].city|escape:'htmlall':'UTF-8'}
                                            {else}
                                                --
                                            {/if}
                                        </td>
                                        <td class="center">
                                            {if $table_data[ii].address}
                                                {$table_data[ii].address|escape:'htmlall':'UTF-8'}
                                            {else}
                                                --
                                            {/if}
                                        </td>
                                        <td style="white-space: nowrap;" class="center">
                                            <a title="{l s='View' mod='dpdpoland'}" href="{$order_link|escape:'htmlall':'UTF-8'}&id_order={$table_data[ii].id_order|escape:'htmlall':'UTF-8'}">
                                                <img alt="{l s='View' mod='dpdpoland'}" src="../img/admin/details.gif">
                                            </a>
                                        </td>
                                    </tr>
                                {/section}
                            {else}
                                <tr>
                                    <td colspan="11" class="center">
                                        {l s='No packages' mod='dpdpoland'}
                                    </td>
                                </tr>
                            {/if}
                        </tbody>
                    </table>
                    <p>
                        <input class="button" type="submit" onclick="return confirm('{l s='Print selected manifest(s)?' mod='dpdpoland'}');" value="{l s='Manifest printout' mod='dpdpoland'}" name="printManifest" />
                        <input class="button" type="submit" value="{l s='Label duplicate printout Label printer' mod='dpdpoland'}" name="printLabelsLabelFormat" />
						<input class="button" type="submit" value="{l s='Label duplicate printout A4' mod='dpdpoland'}" name="printLabelsA4Format" />
                    </p>
                </td>
            </tr>
        </tbody>
    </table>
</form>