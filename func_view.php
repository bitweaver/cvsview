<?php

/**
 * This source code is distributed under the terms as layed out in the
 * GNU General Public License.
 *
 * Purpose: To provide File View Page.
 *
 * Based on phpcvsview
 * @author Brian A Cheeseman <bcheesem@users.sourceforge.net>
 * @copyright 2003-2005 Brian A Cheeseman
 * 
 * Thanks To:
 * 		Nigel McNie - Suggestion of Caching of source code from repository, hence improving efficiency.
 * 
 * Ported to bitweaver framework by Lester Caine 2006-12-29
 * @version $Id: func_view.php,v 1.3 2009/06/18 06:10:59 lsces Exp $
 **/

//include_once( UTIL_PKG_PATH . 'geshi/geshi.php' );

function DisplayFileContents($File, $Revision = "")
{
	global $gBitSmarty, $config, $env;

	// Create our CVS connection object and set the required properties.
	$CVSServer = new CVS_PServer($env['CVSSettings']['cvsroot'], $env['CVSSettings']['server'], $env['CVSSettings']['username'], $env['CVSSettings']['passwd']);

	// Check and see if this file and version has already been viewed and exists in the cache.
	$CachedFileName = $config['Cache']['Location'];
	if ($config['Cache']['Enable']) {
		if (!file_exists($CachedFileName)) {
		    mkdir($CachedFileName, 0750);
		}
		$CachedFileName .= "/".str_replace("/", "_", $File).",$Revision";
	}

	if (file_exists($CachedFileName) && $config['Cache']['Enable']) {
		$fd = fopen($CachedFileName, "r");
		if ($fd !== false) {
			fpassthru($fd);
			fclose($fd);
		}
	}
	else
	{
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
/*	
			if ($config['GeSHi']['Enable']) {
				// Create the GeSHi instance and parse the output.
				// TODO: setup code to auto identify the highlighting class to use for current file.
				$FileExt = substr($File, strrpos($File, '.')+1);
				$Language = guess_highlighter($FileExt);
				if (is_array($Language)) {
					$Language = $Language[0];
				}
				
				$geshi = new GeSHi($CVSServer->FILECONTENTS, $Language, $config['GeSHi']['HighlightersPath']);
				$geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
				$geshi->set_line_style('background: #fcfcfc;'); 
				$geshi->set_tab_width(4);
				$hlcontent = $geshi->parse_code();
	
				// Store in the current cache.		
				if ($config['Cache']['Enable']) {
					$fd = fopen($CachedFileName, "w");
					if ($fd !== false) {
						fwrite($fd, '<p class="source">'.$hlcontent.'</p>');
						fclose($fd);
					}
				}		

				// Display the file contents.
				echo '<p class="source">';
				echo $hlcontent;
				echo '</p>';
			}
			else
			{

				$search = array('<', '>', '\n', '\t');
				$replace = array('&lt;', '&gt;', '', ' ');
				$content = str_replace($search, $replace, $CVSServer->FILECONTENTS);
				$source = explode('\n', $content);
				$soure_size = sizeof($source);
				
				if ($config['Cache']['Enable']) {
					$fd = fopen($CachedFileName, "w");
					if ($fd !== false) {
						fwrite($fd, "<pre>\n");
					}
				}
				else
				{
					$fd = false;
				}

				echo "<pre>\n";
				for($i = 1; $i <= $soure_size; $i++) {
					$line = '<a name="'.$i.'" class="numberedLine">&nbsp;'.str_repeat('&nbsp;', strlen($soure_size) - strlen($i)). $i.'.</a> ' . $source[$i-1] . "\n";
					if ($fd !== false) {
						fwrite($fd, $line);
					}
					echo $line;
				}

				if ($fd !== false) {
					fwrite($fd, "</pre>\n");
				}
				echo "</pre>\n";
			}
*/
			// Close the connection.
			$CVSServer->Disconnect();
		} else {
			$gBitSmarty->assign('error', "ERROR: Could not connect to the PServer." );
		}
	}
}

?>
