<?php

/**
 * This source code is distributed under the terms as layed out in the
 * GNU General Public License.
 *
 * Purpose: To provide File Download capability.
 *
 * Based on phpcvsview
 * @author Brian A Cheeseman <bcheesem@users.sourceforge.net>
 * @copyright 2003-2005 Brian A Cheeseman
 * 
 * Ported to bitweaver framework by Lester Caine 2006-12-29
 * @version $Id$
 **/
 
function DownloadFile($File, $Revision = "")
{
	global $env, $MIME_TYPES;

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

		// "Export" the file.
		$Response = $CVSServer->ExportFile($File, $Revision);
		if ($Response !== true) {
			return;
		}
		
		// Get the mime type for the file.
		$FileExt = substr($File, strrpos($File, '.')+1);
		$MimeType = $MIME_TYPES[$FileExt];
		if ($MimeType == '') {
			$MimeType = 'text/plain';
		}
		
		// Send the appropriate http header.
		header('Content-Type: '.$MimeType);
		
		// Send the file contents.
		echo $CVSServer->FILECONTENTS;
		echo '<br />File Extension: '.$FileExt;
		echo '<br />Mime Type is: '. $MimeType;

		// Close the connection.
		$CVSServer->Disconnect();
	} else{
		echo '<h2>ERROR: Could not connect to the PServer.</h2>';
	}
}

?>
