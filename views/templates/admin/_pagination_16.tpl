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
			/ {$list_total} {l s='result(s)' mod='dpdpoland'}
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
				$('#submitFilter'+'{$identifier}').val($(this).data("page")).closest("form").submit();
			});
		</script>
	</div>
</div>
{/if}