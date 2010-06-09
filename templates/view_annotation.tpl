{* $Header$ *}
	<div class="header">
		<h1>{if $pagetitle ne ''}{$pagetitle}{else}{tr}File Annotation{/tr}{/if}</h1>
	</div>

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
