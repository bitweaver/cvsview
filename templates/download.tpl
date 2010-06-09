{* $Header$ *}
<div class="display cvsview {$pageInfo.title|escape|lower|regex_replace:"/[^a-z_]/i":""}">
	{include file="bitpackage:cvsview/page_header.tpl"}

	{if $print_page ne 'y'}
		{include file="bitpackage:cvsview/page_action_bar.tpl"}
	{/if}
</div><!-- end .cvsview -->
