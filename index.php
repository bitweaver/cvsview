<?php
/*
 * This source code is distributed under the terms as layed out in the
 * GNU General Public License.
 *
 * Purpose: To provide the main entry point in accessing a CVS repository
 *
 * Based on phpcvsview
 * @author Brian A Cheeseman <bcheesem@users.sourceforge.net>
 * @copyright 2003-2005 Brian A Cheeseman
 * 
 * Ported to bitweaver framework by Lester Caine 2006-12-29
 * @version $Id$
 */

// Initialization
require_once 'config.php';
require_once( '../kernel/setup_inc.php' );

	require_once ( CVSVIEW_PKG_PATH.'PhpCvs.php' );
	require_once ( CVSVIEW_PKG_PATH.'phpcvsmime.php' );

global $config, $env;

// set enviroment paths and defaults
$env['script_name'] = $_SERVER['PHP_SELF'];

$env['script_path'] = substr($env['script_name'], 0, strrpos($env['script_name'], "/"));
$env['script_path'] = (empty($env['script_path']))? '/' : $env['script_path'];

$env['mod_path'] = (isset($_GET["mp"])) ? $_GET["mp"] : "/";
$env['mod_path'] = str_replace("//", "/", $env['mod_path']);

$gBitSmarty->assign('root', $env['script_name'] );

$Dirs = explode("/", $env['mod_path']);
$intCount = 1;
$OffSet = 1;
// Need to tweek for file or folder as last element
//if ($LastIsFile) {
//	$OffSet = 1;
//}

$links = array();
while($intCount < count($Dirs)-$OffSet) {
	if (($intCount != count($Dirs)-$OffSet)) {
		$links[$intCount-1]['link'] = ImplodeToPath($Dirs, "/", $intCount);
		$links[$intCount-1]['name'] = $Dirs[$intCount];
	} 
	$intCount++;
}
$gBitSmarty->assign('last', $Dirs[$intCount] );
$gBitSmarty->assign_by_ref('links', $links );

// Determine the CVSROOT settings required for this instance.
$env['CVSROOT'] = (empty($_COOKIE['config']['CVSROOT'])) ? $config['default_cvs'] : $_COOKIE['config']['CVSROOT'];
if (isset($_GET["cr"])) {
	$env['mod_path'] = "/";
	unset($_GET["fh"]);
        unset($_GET["fa"]);
        unset($_GET["fv"]);
        unset($_GET["fd"]);
        unset($_GET["df"]);
        unset($_GET["dp"]);
	$env['CVSROOT'] = $_GET["cr"];
	// Set cookie with theme info. This cookie is set to expire 1 year from today.
	setcookie("config[CVSROOT]", $env['CVSROOT'], time()+31536000, "/");
}
$env['CVSSettings'] = $config['cvs'][$env['CVSROOT']];

// include required files and functions
require_once 'func_dir_listing.php';
require_once 'func_history.php';
require_once 'func_annotation.php';
require_once 'func_view.php';
require_once 'func_download.php';
require_once 'func_diff_file.php';
require_once 'func_archive_download.php';

// begin display logic
if (isset($_GET["fh"])) {
	DisplayFileHistory();
	$gBitSystem->display( 'bitpackage:cvsview/view_history.tpl', tra( 'Annotation history for: ' ) , array( 'display_mode' => 'display' ));
} else {
	if (isset($_GET["fa"])) {
		 DisplayFileAnnotation($env['mod_path'], $_GET["fa"]);
		 $gBitSystem->display( 'bitpackage:cvsview/view_annotation.tpl', tra( 'Annotation history for: ' ) , array( 'display_mode' => 'display' ));
	} else {
		if (isset($_GET["fv"])) {
			DisplayFileContents($env['mod_path'], $_GET["dt"]);
			$gBitSystem->display( 'bitpackage:cvsview/view_contents.tpl', tra( 'Revision history for: ' ) , array( 'display_mode' => 'display' ));
		} else {
			if (isset($_GET["fd"])) {
			    DownloadFile($env['mod_path'], $_GET["dt"]);
			    $gBitSystem->display( 'bitpackage:cvsview/download.tpl', tra( 'download: ' ) , array( 'display_mode' => 'display' ));
			} else {
				if (isset($_GET["df"])) {
				    DisplayFileDiff($_GET["r1"], $_GET["r2"]);
				    $gBitSystem->display( 'bitpackage:cvsview/view_diff.tpl', tra( 'Revision Diff for: ' ) , array( 'display_mode' => 'display' ));
				} else {
					if (isset($_GET["dp"])) {
					    DownloadArchive();
					} else {
						DisplayDirListing();
						$gBitSystem->display( 'bitpackage:cvsview/list_dir.tpl', tra( 'CVS Archive: ' ) , array( 'display_mode' => 'display' ));
					}
				}
			}
		}
	}
}

?>