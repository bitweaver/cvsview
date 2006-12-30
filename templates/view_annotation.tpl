{* $Header: /cvsroot/bitweaver/_bit_cvsview/templates/view_annotation.tpl,v 1.1 2006/12/30 13:39:34 lsces Exp $ *}
{strip}
<div class="display cvsview {$pageInfo.title|escape|lower|regex_replace:"/[^a-z_]/i":""}">
	{include file="bitpackage:cvsview/page_header.tpl"}

<div id="fileannotation">
	{formfeedback error=$errors}
		<table>
			{cycle values="even,odd" print=false}
			{section name=line loop=$lines}
				<tr class="{cycle advance=false}">
					<td>{$files[line].Revision}</td>
					<td>{$lines[line].Date}</td>
					<td>{$lines[line].Author}</td>
					<td>{$lines[line].Text}<td>
				</tr>
			{/section}
		</table>
</div>
	{if $print_page ne 'y'}
		{include file="bitpackage:cvsview/page_action_bar.tpl"}
	{/if}
</div><!-- end .cvsview -->
{/strip}
