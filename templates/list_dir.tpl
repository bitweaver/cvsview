{* $Header$ *}
{strip}
<div class="display cvsview">
	<div class="header">
		<h1>{if $pagetitle ne ''}{$pagetitle}{else}{tr}Directory Listing{/tr}{/if}</h1>
	</div>

	{include file="bitpackage:cvsview/page_header.tpl"}

	{formfeedback error=$errors}
	
		<table>
			<tr class="head">
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th>File</th>
				<th>Rev</th>
				<th>Age</th>
				<th>Author</th>
				<th>Last Log Entry</th>
			</tr>

			{if $hrefup}
				{cycle values="even,odd" print=false}
				<tr class="{cycle advance=false}">
					<td class="min">&nbsp;</td>
					<td class="min"><a href="{$hrefup}"><img alt="Up" src="{$ParentIcon}" /></a></td>
					<td class="min"><a href="{$hrefup}">Up one folder</a></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
			{/if}

			{cycle values="even,odd" print=false}
			{section name=folder loop=$folders}
				<tr class="{cycle advance=false}">
					{if ($folders[folder].Name != "CVSROOT" && $folders[folder].Name != "Attic") }
						<td class="min"><a href="{$HREF}&amp;dp"><img alt="D/L" src="{$DownloadIcon}" /></a></td>
					{else}
						<td class="min">&nbsp;</td>
					{/if}
					<td class="min"><a href="{$HREF}{$folders[folder].Name}"><img alt="Dir" src="{$FolderIcon}" /></a></td>
					<td class="min"><a href="{$HREF}{$folders[folder].Name}">{$folders[folder].Name}</a></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
			{/section}

			{if $modules}
				{cycle values="even,odd" print=false}
				{section name=module loop=$modules}
					<tr class="{cycle advance=false}">
						{if ($modules[module].Name != "CVSROOT" && $modules[module].Name != "Attic") }
							<td class="min"><a href="{$HREF}&amp;dp"><img alt="D/L" src="{$DownloadIcon}" /></a></td>
						{else}
							<td class="min">&nbsp;</td>
						{/if}
						<td class="min"><a href="{$HREF}{$modules[module].Name}"><img alt="Dir" src="{$ModuleIcon}" /></a></td>
						<td class="min"><a href="{$HREF}{$modules[module].Name}">{$modules[module].Name}</a></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
				{/section}
			{/if}

			{cycle values="even,odd" print=false}
			{section name=file loop=$files}
				<tr class="{cycle advance=false}">
					<td class="min">&nbsp;</td>
					<td class="min"><a href="{$files[file].HREF}&amp;fh"><img alt="Dir" src="{$FileIcon}" /></a></td>
					<td class="min"><a href="{$files[file].HREF}&amp;fh">{$files[file].Name}</a></td>
					<td align="center"><a href="{$HREF}&amp;fv&amp;dt={$files[DateTime]}">{$files[file].Head}</a></td>
					<td align="center">{$files[file].AGE}&nbsp;Ago</td>
					<td align="center">{$files[file].Author}</td>
					<td>{$files[file].Log}</td>
				</tr>
			{/section}

		</table>

	{if $print_page ne 'y'}
		{include file="bitpackage:cvsview/page_action_bar.tpl"}
	{/if}
</div><!-- end .cvsview -->
{/strip}
