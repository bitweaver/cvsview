<?php

/**
 * This source code is distributed under the terms as layed out in the
 * GNU General Public License.
 *
 * Purpose: To provide File Annotation Page.
 *
 * Based on phpcvsview
 * @author Brian A Cheeseman <bcheesem@users.sourceforge.net>
 * @copyright 2003-2005 Brian A Cheeseman
 * 
 * Ported to bitweaver framework by Lester Caine 2006-12-29
 * @version $Id: func_annotation.php,v 1.1 2006/12/30 13:30:44 lsces Exp $
 **/

function DisplayFileAnnotation($File, $Revision = "")
{
	global $gBitSmarty, $env;

	// Create our CVS connection object and set the required properties.
	$CVSServer = new CVS_PServer($env['CVSSettings']['cvsroot'], $env['CVSSettings']['server'], $env['CVSSettings']['username'], $env['CVSSettings']['password']);

	// Connect to the CVS server.
	if ($CVSServer->Connect() === true) {

		// Authenticate against the server.
		$Response = $CVSServer->Authenticate();
		if ($Response !== true) {
			return;
		}

		// Annotate the file.
		$Response = $CVSServer->Annotate($File, $Revision);
		if ($Response !== true) {
			return;
		}

vd($CVSServer->ANNOTATION);

		$search = array('<', '>', '\n');
		$replace = array('&lt;', '&gt;', '');
		$PrevRev = "";
		$FirstLine = true;
		$lines = array();
		$i = 0;
		foreach ($CVSServer->ANNOTATION as $Annotation)	{
			if ( $i == 0 or strcmp($PrevRev, $Annotation["Revision"]) != 0) {
				$lines[$i]['Revision'] = $Annotation["Revision"];
				$lines[$i]['Author'] = $Annotation["Author"];
				$lines[$i]['Date'] = $Annotation["Date"];
	 			$lines[$i]['Text'] = $Annotation["Line"];
	 			$j = $i;
	 			$i++;
			}
			str_replace($search, $replace, $Annotation["Line"]);
			$lines[$j]['Text'] .= $Annotation["Line"];
			$PrevRev = $Annotation["Revision"];
		}
		$gBitSmarty->assign_by_ref('lines', $lines);
	
		// Close the connection.
		$CVSServer->Disconnect();
	} else{
		$gBitSmarty->assign('error', "ERROR: Could not connect to the PServer." );
	}
}

?>
