<?php

/**
 * This source code is distributed under the terms as layed out in the
 * GNU General Public License.
 *
 * Purpose: To provide File Listing Page.
 *
 * Based on phpcvsview
 * @author Brian A Cheeseman <bcheesem@users.sourceforge.net>
 * @copyright 2003-2005 Brian A Cheeseman
 * 
 * Ported to bitweaver framework by Lester Caine 2006-12-29
 * @version $Id$
 **/

function DisplayFileHistory()
{
	global $gBitSmarty, $env, $CVSServer;

	// Create our CVS connection object and set the required properties.
	$CVSServer = new CVS_PServer($env['CVSSettings']['cvsroot'], $env['CVSSettings']['server'], $env['CVSSettings']['username'], $env['CVSSettings']['passwd']);

	// Connect to the CVS server.
	if ($CVSServer->Connect() === true) {

		// Authenticate against the server.
		$Response = $CVSServer->Authenticate();
		if ($Response !== true) {
			return;
		}

		// Get a RLOG of the module path specified in $env['mod_path'].
		$CVSServer->RLog($env['mod_path']);

		$Files = $CVSServer->FILES;

		$HREF = str_replace('//', '/', $env['script_name'].'?mp='.$env['mod_path']);
		$gBitSmarty->assign('HREF', $HREF );
		$history = array();
		$i = 0;
		foreach ($CVSServer->FILES[0]["Revisions"] as $Revision) {
			$DateTime = strtotime($Revision["date"]);
			$history[$i]['Revision'] = $Revision["Revision"];
			$history[$i]['DateTime'] = $DateTime;
			if (isset($Revision["PrevRevision"]) and $Revision["PrevRevision"] != '') {
				$history[$i]['PrevRevision'] = $Revision["PrevRevision"];
				if (isset($Revision["lines"]) )
					$history[$i]['lines'] = $Revision["lines"];
			}	
			$history[$i]['Last'] = strftime("%A %d %b %Y %T -0000", $DateTime).' ('.CalculateDateDiff($DateTime, strtotime(gmdate("M d Y H:i:s"))).' ago)';
			$history[$i]['Branches'] = $Revision["Branches"];
			$history[$i]['date'] = strftime("%B %d, %Y", $DateTime);
			$history[$i]['time'] = strftime("%H:%M:%S", $DateTime);
			$history[$i]['author'] = $Revision["author"];
			$history[$i]['state'] = $Revision["state"];
			$history[$i]['LogMessage'] = $Revision["LogMessage"];
			$i++;
		}

		$gBitSmarty->assign_by_ref('histories', $history);

		$revs = array();
		foreach ($CVSServer->FILES[0]["Revisions"] as $Revision){
			$revs[] = $Revision["Revision"];
		}
		$gBitSmarty->assign_by_ref('revs', $revs);

		$CVSServer->Disconnect();
	} else {
		$gBitSmarty->assign('errors', "ERROR: Could not connect to the PServer." );
	}
}

?>
