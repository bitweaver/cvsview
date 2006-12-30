<form class="diffform" action="{$HREF}">
	Diff between: 
	<select name="DiffRev1" class="diffform">
	{section name=rev loop=$revs}
		<option value="{$revs[rev]}">{$revs[rev]}</option>
	{/section}
	</select> and 
	<select name="DiffRev2" class="diffform">
	{section name=rev loop=$revs}
		<option value="{$revs[rev]}">{$revs[rev]}</option>
	{/section}
	</select>
	<input type="hidden" name="URLDiffReq" value="{$HREF}">
	<input type="button" value="Get Diff" onclick="postBackDiffRequest(this.form)">
</form>
