{* $Header$ *}
<div class="floaticon">{bithelp}</div>

<div id="filehistory">
	<div class="header">
		<h1>{if $pagetitle ne ''}{$pagetitle}{else}{tr}File History{/tr}{/if}</h1>
	</div>

	{include file="bitpackage:cvsview/page_header.tpl"}

	{formfeedback error=$errors}

			{section name=line loop=$histories}
				<div id="filerevision">
					<p><b>Revision</b> {$histories[line].Revision}&nbsp;
					<a href="{$HREF}&amp;fv&amp;dt={$histories[line].DateTime}">view</a>&nbsp;
					<a href="{$HREF}&amp;fd&amp;dt={$histories[line].DateTime}">download</a>&nbsp;
					{if $histories[line].$PrevRevision }
						(<a href="{$HREF}&amp;df&amp;r1={$histories[line].$PrevRevision}&amp;r2={$histories[line].$PrevRevision}">diff to previous</a>)&nbsp;
					{/if}
					(<a href="{$HREF}&amp;fa={$histories[line].Revision}">annotate</a>)</p>
					<p><b>Last Checkin:</b> {$histories[line].Last} <b>Branch</b> {$histories[line].Branches}</p>
					<p><b>Date</b> {$histories[line].date} <b>Time</b> {$histories[line].time}</p>
					<p><b>Author</b> {$histories[line].author}</p>
					<p><b>State</b> {$histories[line].state}</p>
					<p><b>Changes {$histories[line].$PrevRevision}:</b>
					{if $histories[line].$PrevRevision }
						<p><b>Changes {$histories[line].$PrevRevision}:</b>
						{if $histories[line].lines } 
							{$histories[line].lines}
						{/if}
						</p>
					{/if}
					<p><b>Log Message:</b></p>
					<p class="logmsg">{$histories[line].LogMessage}</p>
					<hr />
				</div>
			{/section}
			
{include file="bitpackage:cvsview/diff_form.tpl"}

</div><!-- end .filehistory -->
