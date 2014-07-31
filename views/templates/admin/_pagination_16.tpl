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
{if $list_total > 10}
<div class="row">
	<div class="col-lg-4 pull-right clearfix">

		<span class="pagination">
			{l s='Display:' mod='dpdpoland'}
			<button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button">
				{$selected_pagination|intval}
				<i class="icon-caret-down"></i>
			</button>
			<ul class="dropdown-menu">
				{foreach $pagination AS $value}
					<li>
						<a href="javascript:void(0);" class="pagination-items-page" data-items="{$value|intval}">{$value}</a>
					</li>
				{/foreach}
			</ul>
			/ {$list_total|intval} {l s='result(s)' mod='dpdpoland'}
			<input type="hidden" value="{$selected_pagination|intval}" name="country_pagination" id="pagination-items-page">
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
				<a href="javascript:void(0);" class="pagination-link" data-page="{$page|intval - 1}">
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
						<a href="javascript:void(0);" class="pagination-link" data-page="{$p|intval}">{$p|intval}</a>
					</li>
				{/if}
			{/while}
			<li {if $page > $total_pages}class="disabled"{/if}>
				<a href="javascript:void(0);" class="pagination-link" data-page="{$page|intval + 1}">
					<i class="icon-angle-right"></i>
				</a>
			</li>
			<li {if $page > $total_pages}class="disabled"{/if}>
				<a href="javascript:void(0);" class="pagination-link" data-page="{$total_pages|intval}">
					<i class="icon-double-angle-right"></i>
				</a>
			</li>
		</ul>
		<script type="text/javascript">
			$('.pagination-link').on('click',function(e){
				e.preventDefault();
				$('#submitFilter'+'{$identifier|escape:'htmlall':'UTF-8'}').val($(this).data("page")).closest("form").submit();
			});
		</script>
	</div>
</div>
{/if}