{* $Header: /cvsroot/bitweaver/_bit_cvsview/templates/header_inc.tpl,v 1.1 2006/12/30 13:39:34 lsces Exp $ *}
{if $smarty.const.ACTIVE_PACKAGE == 'cvsview'}
	<link rel="stylesheet" title="{$style}" type="text/css" href="{$smarty.const.CVSVIEW_PKG_URL}styles/theme.css" media="all" />
	<script src="{$smarty.const.CVSVIEW_PKG_URL}phpcvsview.js" type="text/javascript"></script>
{/if}
