{* $Header: /cvsroot/bitweaver/_bit_cvsview/templates/view_diff.tpl,v 1.1 2006/12/30 13:39:34 lsces Exp $ *}
<div class="display cvsview {$pageInfo.title|escape|lower|regex_replace:"/[^a-z_]/i":""}">
	{include file="bitpackage:cvsview/page_header.tpl"}

	{if $print_page ne 'y'}
		{include file="bitpackage:cvsview/page_action_bar.tpl"}
	{/if}
</div><!-- end .cvsview -->
