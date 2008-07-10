<?php

/**
 * This source code is distributed under the terms as layed out in the
 * GNU General Public License.
 *
 * Purpose: To provide Directory Listing Page.
 *
 * Based on phpcvsview
 * @author Brian A Cheeseman <bcheesem@users.sourceforge.net>
 * @copyright 2003-2005 Brian A Cheeseman
 * 
 * Ported to bitweaver framework by Lester Caine 2006-12-29
 **/

function DisplayDirListing()
{
	global $gBitSmarty, $env;

	// Create our CVS connection object and set the required properties.
	$CVSServer = new CVS_PServer($env['CVSSettings']['cvsroot'], $env['CVSSettings']['server'], $env['CVSSettings']['username'], $env['CVSSettings']['passwd']);

	// Connect to the CVS server.
	if ($CVSServer->Connect() === true) {

		// Authenticate against the server.
		$Response = $CVSServer->Authenticate();
		if ($Response !== true) {
			$gBitSmarty->assign('error', "ERROR: ".$Response);
			return;
		}

		// Get a RLOG of the module path specified in $env['mod_path'].
		$CVSServer->RLog($env['mod_path']);
		
		// If we are in the Root of the CVS Repository then lets get the Module list.
		if (strlen($env['mod_path']) < 2) {
			$Modules = $CVSServer->getModuleList();
		}
		else
		{
			$Modules = false;
		}

		// Do we need the "Back" operation.
		if (strlen($env['mod_path']) > 2) {
			$hrefup = str_replace("//", "/", $env['script_name']."?mp=".substr($env['mod_path'], 0, strrpos(substr($env['mod_path'], 0, -1), "/"))."/");
			$gBitSmarty->assign('hrefup', $hrefup );
			$gBitSmarty->assign('ParentIcon', $env['script_path'].'/icons/parent.png' );
		}

		$HREF = str_replace("//", "/", $env['script_name']."?mp=".$env['mod_path']."/");
		$gBitSmarty->assign('HREF', $HREF );
		$gBitSmarty->assign('DownloadIcon', $env['script_path'].'/icons/download.png' );
		$gBitSmarty->assign('FolderIcon', $env['script_path'].'/icons/folder.png' );
		$gBitSmarty->assign('ModuleIcon', $env['script_path'].'/icons/module.png' );
		$gBitSmarty->assign('FileIcon', $env['script_path'].'/icons/file.png' );
		$gBitSmarty->assign_by_ref('folders', $CVSServer->FOLDERS);
		if ($Modules !== false) {
			$gBitSmarty->assign_by_ref('modules', $Modules);
		}
		$lfiles = array();
		$i = 0;
		foreach ($CVSServer->FILES as $File) {
			$lfiles[$i]['Name'] = $File['Name'];
			$lfiles[$i]['Head'] = $File['Head'];
			$lfiles[$i]['HREF'] = str_replace("//", "/", $env['script_name']."?mp=".$env['mod_path']."/".$File["Name"]);
			$lfiles[$i]['DateTime'] = strtotime($File["Revisions"][$File["Head"]]["date"]);
			$lfiles[$i]['AGE'] = CalculateDateDiff($lfiles[$i]['DateTime'], strtotime(gmdate("M d Y H:i:s")));
			$lfiles[$i]['Author'] = $File["Revisions"][$File["Head"]]["author"];
			$lfiles[$i]['Log'] = $File["Revisions"][$File["Head"]]["LogMessage"];
			$i++;
		}
		$gBitSmarty->assign_by_ref('files', $lfiles);

		$CVSServer->Disconnect();
	} else {
		$gBitSmarty->assign('error', "ERROR: Could not connect to the PServer" );
	}
}

?>
