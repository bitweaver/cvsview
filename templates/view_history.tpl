{* $Header: /cvsroot/bitweaver/_bit_cvsview/templates/view_history.tpl,v 1.2 2006/12/30 15:07:53 lsces Exp $ *}
{strip}
<div class="floaticon">{bithelp}</div>

<div id="filehistory">
	<div class="header">
		<h1>{if $pagetitle ne ''}{$pagetitle}{else}{tr}File History{/tr}{/if}</h1>
	</div>

	{formfeedback error=$errors}

			{section name=line loop=$histories}
				<div id="filerevision">
					<p><b>Revision</b> {$histories[line].Revision}&nbsp;
					(<a href="{$HREF}&amp;fa={$histories[line].Revision}">annotate</a>)&nbsp;
					<a href="{$HREF}&amp;fv&amp;dt={$histories[line].DateTime}">view</a>&nbsp;
					<a href="{$HREF}&amp;fd&amp;dt={$histories[line].DateTime}">download</a>&nbsp;
					{if $histories[line].$PrevRevision }
						(<a href="{$HREF}&amp;df&amp;r1={$histories[line].$PrevRevision}&amp;r2={$histories[line].$PrevRevision}">diff to previous</a>)&nbsp;
					{/if}
					(<a href="{$HREF}&amp;fa={$histories[line].Revision}">annotate</a>)</p>
					<p><b>Last Checkin:</b> {$histories[line].Last}</p>
					<p><b>Branch</b> {$histories[line].Branches}</p>
					<p><b>Date</b> {$histories[line].date}</p>
					<p><b>Time</b> {$histories[line].time}</p>
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
					<p class="logmsg">{$histories[history].LogMessage}</p>
					<hr />
				</div>
			{/section}
			
{include file="bitpackage:cvsview/diff_form.tpl"}

</div><!-- end .filehistory -->
{/strip}
