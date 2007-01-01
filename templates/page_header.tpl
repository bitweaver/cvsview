<div class="header">
	{if $gBitSystem->isFeatureActive( 'cvs_page_title' )}
		<h1>{$pageInfo.title|escape}</h1>
		{if $pageInfo.page_is_cached}<span class="cached">(cached)</span>{/if}
	{/if}

	{if $gBitSystem->isFeatureActive( 'cvs_description' ) and $description}
		<h2>{$pageInfo.description|escape}</h2>
	{/if}
	
<div class="quicknav">
<a href="{$root}">Root</a>
{section name=link loop=$links}
	>>&nbsp;<a href="{$root}?mp={$links[link].link}">{$links[link].name}</a>
{/section}
{if $last != ''}
	>>&nbsp;{$last}
{/if}
</div>
</div><!-- end .header -->
