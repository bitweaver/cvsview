<?php

/**
 * This source code is distributed under the terms as layed out in the
 * GNU General Public License.
 *
 * Purpose: To provide the HTML page header code
 *
 * @author Brian A Cheeseman <bcheesem@users.sourceforge.net>
 * @version $Id: theme.php,v 1.1 2006/12/30 13:30:44 lsces Exp $
 * @copyright 2003-2005 Brian A Cheeseman
 **/

function GetQuickLinkBar($Prefix = "", $LinkLast = false, $LastIsFile = false, $Revision = "")
{
	global $env;

	if(empty($Prefix)){ $Prefix = 'Navigate to: ';}
	// Add the quick link navigation bar.
	$Dirs = explode("/", $env['mod_path']);
	$QLOut = '<div class="quicknav">'.$Prefix.'<a href="'.$env['script_name'].'">Root</a>&nbsp;';
	$intCount = 1;
	$OffSet = 2;
	if ($LastIsFile) {
		$OffSet = 1;
	}

	while($intCount < count($Dirs)-$OffSet) {
		if (($intCount != count($Dirs)-$OffSet)) {
			$QLOut .= '/&nbsp;<a href="'.$env['script_name'].'?mp='.ImplodeToPath($Dirs, "/", $intCount).'/">'.$Dirs[$intCount].'</a>&nbsp;';
		} else {
			$QLOut .= '/&nbsp;'.$Dirs[$intCount].'&nbsp;';
		}
		$intCount++;
	}

	$QLOut .= '/&nbsp;';
	if ($LinkLast) {
		$QLOut .= '<a href="'.$env['script_name'].'?mp='.ImplodeToPath($Dirs, "/", $intCount);
		if ($LastIsFile) {
			$QLOut .= '&amp;fh#rd'.$Revision.'">';
		} else {
			$QLOut .= '/';
		}
	}

	$QLOut .= $Dirs[$intCount];
	if ($LinkLast) {
		$QLOut .= '</a>';
	}
	$QLOut .= '</div>'."\n";

	return $QLOut;
}

?>
