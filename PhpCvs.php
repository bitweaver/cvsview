<?php
error_reporting(E_ALL);

/**
 * This source code is distributed under the terms as layed out in the
 * GNU General Public License.
 *
 * Purpose: To provide the main class required to access a CVS repository
 *
 * Based on phpcvsview
 * @author Brian A Cheeseman <bcheesem@users.sourceforge.net>
 * @copyright 2003-2005 Brian A Cheeseman
 * 
 * Ported to bitweaver framework by Lester Caine 2006-12-29
 * @version $Id: PhpCvs.php,v 1.1 2006/12/30 13:30:44 lsces Exp $
 **/

/**
 * Part of the PEAR packages, and is used to enable TCP communication with the 
 * hosting CVS pserver.
 */
require_once(UTIL_PKG_PATH.'pear/Net/Socket.php');
require_once 'utils.php';

/**
 * The CVS_PServer class provides a simplified interface to the CVS pserver.
 * @subpackage PServer
 */
class CVS_PServer
{
    /**
     * Storage of the CVS Repository file system path.
     * @access private
     * @var string
     */
	var $CVS_REPOSITORY;
	
	/**
	 * Username to use when authenticating with the PServer.
	 * @access private
	 * @var string
	 * @see $CVS_PASSWORD
	 */
	var $CVS_USERNAME;
	
	/**
	 * Password for the account specified in $CVS_USERNAME.
	 * @access private
	 * @var string
	 * @see $CVS_USERNAME
	 */
	var $CVS_PASSWORD;
	
	/**
	 * Hostname of the server providing PServer services.
	 * @access private
	 * @var string
	 */
	var $CVS_PSERVER;
	
	/**
	 * Port number the PServer listener is running on.
	 * @access private
	 * @var string
	 */
	var $CVS_PORT;
	
	/**
	 * Timeout in seconds for all socket operations.
	 * @access private
	 * @var string
	 */
	var $CVS_TIMEOUT;
	
	/**
	 * The socket handle for communicating with the PServer.
	 * @access private
	 * @var Net/Socket
	 */
	var $SOCKET;
	
	/**
	 * A hashed array of responses that we are capable of processing and the 
	 * contents is the name of the function to process it through.
	 * @access private
	 * @var array
	 */
	var $ALLOWED_RESPONSES = array( 
		"ok" => "processOk",
		"error" => "processError",
		"Valid-requests" => "processValidRequests",
		"Checked-in" => "processCheckedIn",
		"New-entry" => "processNewEntry",
		"Checksum" => "processChecksum",
		"Copy-file" => "processCopyFile",
		"Updated" => "processUpdated",
		"Created" => "processCreated",
		"Update-existing" => "processUpdateExisting",
		"Merged" => "processMerged",
		"Patched" => "processPatched",
		"Rcs-diff" => "processRcsDiff",
		"Mode" => "processMode",
		"Mod-time" => "processModTime",
		"Removed" => "processRemoved",
		"Remove-entry" => "processRemoveEntry",
		"Set-static-directory" => "processSetStaticDirectory",
		"Clear-static-directory" => "processClearStaticDirectory",
		"Set-sticky" => "processSetSticky",
		"Clear-sticky" => "processClearSticky",
		"Template" => "processTemplate",
		"Set-checkin-prog" => "processSetCheckinProg",
		"Set-update-prog" => "processSetUpdateProg",
		"Notified" => "processNotified",
		"Module-expansion" => "processModuleExpansion",
		"Wrapper-rcsOption" => "processWrapperRcsOption",
		"M" => "processM",
		"Mbinary" => "processMBinary",
		"E" => "processE",
		"F" => "processF",
		"MT" => "processMT"
		);

	/**
	 * List of valid requests the PServer will accept from clients.
	 * @access private
	 * @var array
	 */
	var $ALLOWED_REQUESTS = array();
	
	/**
	 * A state variable for track whether this is the final line we are expecting
	 * to receive from the CVS PServer.
	 * @access private
	 * @var boolean
	 */
	var $FINAL_RESPONSE;

	/*
	 * Standard Error output. (Does not mean that an error occured).
	 * @access private
	 * @var string
	 */
	var $STDERR;
	
	/*
	 * Message contents. (Standard Out)
	 * @access private
	 * @var string
	 */
	var $MESSAGE_CONTENT;
	
	/*
	 * A list of the folders in the current module and/or directory.
	 * @access public
	 * @var array
	 */
	var $FOLDERS = array();
	var $FILES = array();				// An array of the files in the current module.
	var $CURRENT_FOLDER;				// The current folder we are building up.
	var $CURRENT_FILE;					// The current file we are building up.
	var $ANNOTATION = array();			// An array of the lines in the file which has been annotated.
	var $FILECONTENTS = "";				// A string to store the lines of the file contents in.
	var $INITIALISED = false;			// A boolean to indicate whether we have already sent the Root/ValidRequests/ValidResponses.
	var $DEBUG = false;					// A boolean to enable/disable debug output

	/**
	* Allowed Response Decoding functions.
	**/

	// ***************************************************************************
	//     Function: processOk()
	//       Author: Brian A Cheeseman.
	//   Parameters: string			- Line of response text.
	// Return Value: boolean		- Are we expecting more responses to come in?
	// ***************************************************************************
	function processOk($LineOfText)
	{
		$this->FINAL_RESPONSE = true;
		return false;
	}

	// ***************************************************************************
	//     Function: processError()
	//       Author: Brian A Cheeseman.
	//   Parameters: string			- Line of response text.
	// Return Value: boolean		- Are we expecting more responses to come in?
	// ***************************************************************************
	function processError($LineOfText)
	{
		$this->FINAL_RESPONSE = false;
		return false;
	}

	// ***************************************************************************
	//     Function: processValidRequests()
	//       Author: Brian A Cheeseman.
	//   Parameters: string			- Line of response text.
	// Return Value: boolean		- Are we expecting more responses to come in?
	// ***************************************************************************
	function processValidRequests($LineOfText)
	{
		// Convert string to array
		$Token = explode(" ", $LineOfText);
		// Skip the first token, as it is the response identifier.
		array_shift($Token);

		// set values for allowed responses
		for($i = 0; $i <= sizeof($Token)-1; $i++){
			$this->ALLOWED_REQUESTS[$Token[$i]] = true;
		}
		return true;
	}

	// ***************************************************************************
	//     Function: processE()
	//       Author: Brian A Cheeseman.
	//   Parameters: string			- Line of response text.
	// Return Value: boolean		- Are we expecting more responses to come in?
	// ***************************************************************************
	function processE($LineOfText)
	{
		$this->STDERR .= substr($LineOfText, 2) . "\n";
		return true;
	}

	// ***************************************************************************
	//     Function: processM()
	//       Author: Brian A Cheeseman.
	//   Parameters: string			- Line of response text.
	// Return Value: boolean		- Are we expecting more responses to come in?
	// ***************************************************************************
	function processM($LineOfText)
	{
		$this->MESSAGE_CONTENT .= substr($LineOfText, 2) . "\n";
		return true;
	}

	// ***************************************************************************
	//     Function: processClearSticky()
	//       Author: Brian A Cheeseman.
	//   Parameters: string			- Line of response text.
	// Return Value: boolean		- Are we expecting more responses to come in?
	// ***************************************************************************
	function processClearSticky($LineOfText)
	{
		$this->SOCKET->readLine();
		return true;
	}

	// ***************************************************************************
	//     Function: processSetStaticDirectory()
	//       Author: Brian A Cheeseman.
	//   Parameters: string			- Line of response text.
	// Return Value: boolean		- Are we expecting more responses to come in?
	// ***************************************************************************
	function processSetStaticDirectory($LineOfText)
	{
		$this->SOCKET->readLine();
		return true;
	}

	// ***************************************************************************
	//     Function: processModTime()
	//       Author: Brian A Cheeseman.
	//   Parameters: string			- Line of response text.
	// Return Value: boolean		- Are we expecting more responses to come in?
	// ***************************************************************************
	function processModTime($LineOfText)
	{
		return true;
	}

	// ***************************************************************************
	//     Function: processCreated()
	//       Author: Brian A Cheeseman.
	//   Parameters: string			- Line of response text.
	// Return Value: boolean		- Are we expecting more responses to come in?
	// ***************************************************************************
	function processCreated($LineOfText)
	{
		$this->SOCKET->readLine();
		$this->SOCKET->readLine();
		$this->SOCKET->readLine();
		return false;
	}

	/**
	*
	* Class Constructor.
	*
	**/
	function CVS_PServer(	$CVSROOT = "/cvsroot/p/ph/phpcvsview",
							$PServer = "cvs.sourceforge.net",
							$UserName = "anonymous",
							$Password = "") {

		$this->CVS_REPOSITORY = $CVSROOT;
		$this->CVS_PSERVER = $PServer;
		$this->CVS_PORT = 2401;
		$this->CVS_USERNAME = $UserName;
		$this->CVS_PASSWORD = $Password;
		$this->SOCKET = new Net_Socket();
	}

	/**
	*
	* Property Retrieval Functions.
	*
	**/
	function get_Repository()
	{
		return $this->CVS_REPOSITORY;
	}

	function get_UserName()
	{
		return $this->CVS_USERNAME;
	}

	function get_Password()
	{
		return $this->CVS_PASSWORD;
	}

	function get_PServer()
	{
		return $this->CVS_PSERVER;
	}

	/**
	*
	* Property Setting Functions.
	*
	**/
	function set_Repository($NewRepository)
	{
		$this->CVS_REPOSITORY = $NewRepository;
		return true;
	}

	function set_UserName($NewUserName)
	{
		$this->CVS_USERNAME = $NewUserName;
		return true;
	}

	function set_Password($NewPassword)
	{
		$this->CVS_PASSWORD = $NewPassword;
		return true;
	}

	function set_PServer($NewPServer)
	{
		$this->CVS_PSERVER = $NewPServer;
		return true;
	}

	/**
	*
	* Class Methods.
	*
	**/

	// ***************************************************************************
	//     Function: TransformPW()
	//       Author: Brian A Cheeseman.
	//   Parameters: $ClearPW		- The clear text password to be transformed.
	// Return Value: string			- The cipher text of the clear test password.
	// ***************************************************************************
	function TransformPW($ClearPW)
	{

		// Define our constant array to provide a lookup table for the conversion
		// of the clear password to cipher text.
		$NewChars = array(
			'!' => 'x',		'8' => '_',		'N' => '[',		'g' => 'I',
			'"' => '5',		'9' => 'A',		'O' => '#',		'h' => 'c',
			'%' => 'm',		':' => 'p',		'P' => '}',		'i' => '?',
			'&' => 'H',		';' => 'V',		'Q' => '7',		'j' => '^',
			'\'' => 'l',	'<' => 'v',		'R' => '6',		'k' => ']',
			'(' => 'F',		'=' => 'n',		'S' => 'B',		'l' => '\'',
			')' => '@',		'>' => 'z',		'T' => '|',		'm' => '%',
			'*' => 'L',		'?' => 'i',		'U' => '~',		'n' => '=',
			'+' => 'C',		'A' => '9',		'V' => ';',		'o' => '0',
			',' => 't',		'B' => 'S',		'W' => '/',		'p' => ':',
			'-' => 'J',		'C' => '+',		'X' => '\\',	'q' => 'q',
			'.' => 'D',		'D' => '.',		'Y' => 'G',		'r' => ' ',
			'/' => 'W',		'E' => 'f',		'Z' => 's',		's' => 'Z',
			'0' => 'o',		'F' => '(',		'_' => '8',		't' => ',',
			'1' => '4',		'G' => 'Y',		'a' => 'y',		'u' => 'b',
			'2' => 'K',		'H' => '&',		'b' => 'u',		'v' => '<',
			'3' => 'w',		'I' => 'g',		'c' => 'h',		'w' => '3',
			'4' => '1',		'J' => '-',		'd' => 'e',		'x' => '!',
			'5' => '"',		'K' => '2',		'e' => 'd',		'y' => 'a',
			'6' => 'R',		'L' => '*',		'f' => 'E',		'z' => '>',
			'7' => 'Q',		'M' => '{');

		// Initialise the cipher text password local storage variable.
		$CryptPW = '';

		// Loop through each char in the clear text password and add
		// the appropriate character from the lookup table to the
		// cipher text password variable.
		for ($i=0; $i<strlen($ClearPW); $i++) {
			$CryptPW .= $NewChars[substr($ClearPW, $i, 1)];
		}

		// Return the cipher text password to the calling code.
		return $CryptPW;
	}

	// ***************************************************************************
	//     Function: Connect()
	//       Author: Brian A Cheeseman.
	//   Parameters: None.
	// Return Value: boolean		- Were we successful in connecting?
	// ***************************************************************************
	function Connect()
	{
		// Do we have the name of the server to connect to?
		if ($this->CVS_PSERVER != "") {
			// Yes, attempt to connect to the server.
			$retval = $this->SOCKET->connect($this->CVS_PSERVER, $this->CVS_PORT, false, $this->CVS_TIMEOUT);
			// Return to the calling code the fact that we are connected.
			return $retval;
		} else {
			// We need a server name to connect, so return a false.
			return false;
		}
	}

	// ***************************************************************************
	//     Function: Disconnect()
	//       Author: Brian A Cheeseman.
	//   Parameters: None.
	// Return Value: boolean		- Were we successful in connecting?
	// ***************************************************************************
	function Disconnect()
	{
		$retval = $this->SOCKET->disconnect();
		return $retval;
	}

	// ***************************************************************************
	//     Function: Authenticate()
	//       Author: Brian A Cheeseman.
	//   Parameters: None.
	// Return Value: boolean		- Are we authenticated.
	// ***************************************************************************
	function Authenticate()
	{
		// Send the start of authentication request.
		if ($this->SOCKET->write("BEGIN AUTH REQUEST\n") != true) {
			return false;
		}

		// Send the path to the repository we are attempting to connect to.
		if ($this->SOCKET->write($this->CVS_REPOSITORY."\n") != true) {
			return false;
		}

		// Send the user name to authenticate with.
		if ($this->SOCKET->write($this->CVS_USERNAME."\n") != true) {
			return false;
		}

		// Transform and send the password matching the username above.
		if ($this->SOCKET->write("A".$this->TransformPW($this->CVS_PASSWORD)."\n") != true) {
			return false;
		}

		// Send the terminator for the authentication request.
		if ($this->SOCKET->write("END AUTH REQUEST\n") != true) {
			return false;
		}

		// Read the next line to determine if we were successful.
		$response = $this->SOCKET->readLine();
		if ($response == true && strncmp($response, "I LOVE YOU", 10) == 0) {
			return true;
		} else {
			// Retrieve the error message from the PServer.
			$errorMsg = "";
			while(!$this->SOCKET->eof()){
				$line = $this->SOCKET->readLine();
				if ( $this->SOCKET->isError($line) ) {
					$errorMsg .= $line->getMessage();
					$this->SOCKET->disconnect();
					return $errorMsg;
				} else {
					if (strncmp($line, "e ", 2) == 0) {
						$errorMsg .= substr($line, 2);
					}
					if (strncmp($line, "error", 5) == 0) {
						$this->SOCKET->disconnect();
					}
				}
			}

			if ($errorMsg == "") {
				return false;
			} else {
				return $errorMsg;
			}
		}
	}

	// ***************************************************************************
	//     Function: processResponse()
	//       Author: Brian A Cheeseman.
	//   Parameters: None.
	// Return Value: boolean		- Successfully sent.
	// ***************************************************************************
	function processResponse()
	{
		$this->MESSAGE_CONTENT = "";
		$this->STDERR = "";
		$KeepGoing = true;
		$debugMsg = "";

		while($KeepGoing){
			set_time_limit(30);
			$ResponseLine = $this->SOCKET->readLine();
			$Response = explode(" ", $ResponseLine);
			if ($this->DEBUG) {
				$debugMsg .= $ResponseLine."\n";
			}
			if ($Response[0] != "") {
				$Func = $this->ALLOWED_RESPONSES[$Response[0]];
				if (method_exists($this, $Func)) {
					$KeepGoing = $this->$Func($ResponseLine);
				}
			}
		}
		if ($this->DEBUG) {
			 CVS_PServer::debug($debug);
		}
	}

	// ***************************************************************************
	//     Function: sendCVSROOT()
	//       Author: Brian A Cheeseman.
	//   Parameters: None.
	// Return Value: boolean		- Successfully sent.
	// ***************************************************************************
	function sendCVSROOT()
	{
		if ($this->SOCKET->write("Root ".$this->CVS_REPOSITORY."\n") != true) {
			return false;
		}
		return true;
	}

	// ***************************************************************************
	//     Function: sendCASE()
	//       Author: Brian A Cheeseman.
	//   Parameters: None.
	// Return Value: boolean		- Successfully sent.
	// ***************************************************************************
	function sendCASE()
	{
		if ($this->SOCKET->write("Case\n") != true) {
		    return false;
		}
		return true;
	}

	// ***************************************************************************
	//     Function: sendValidResponses()
	//       Author: Brian A Cheeseman.
	//   Parameters: None.
	// Return Value: boolean		- Successfully sent.
	// ***************************************************************************
	function sendValidResponses()
	{
		// Build our list of responses we can process into the format required
		// for the cvs pserver.
		$ValidResponses = "";
		foreach ($this->ALLOWED_RESPONSES as $name => $value) {
			$ValidResponses .= " ".$name;
		}

		// Send the command to the pserver.
		if ($this->SOCKET->write("Valid-responses".$ValidResponses."\n") != true) {
			return false;
		}
		return true;
	}

	// ***************************************************************************
	//     Function: sendValidRequests()
	//       Author: Brian A Cheeseman.
	//   Parameters: None.
	// Return Value: boolean		- Successfully sent and processed.
	// ***************************************************************************
	function sendValidRequests()
	{
		if ($this->SOCKET->write("valid-requests\n") != true) {
			return false;
		}
		$this->processResponse();
		return true;
	}

	// ***************************************************************************
	//     Function: sendUseUnchanged()
	//       Author: Brian A Cheeseman.
	//   Parameters: None.
	// Return Value: boolean		- Successfully sent.
	// ***************************************************************************
	function sendUseUnchanged()
	{
		if ($this->ALLOWED_REQUESTS["UseUnchanged"] == true) {
			if ($this->SOCKET->write("UseUnchanged\n") != true) {
				return false;
			}
		}
		return true;
	}

	// ***************************************************************************
	//     Function: sendArgument()
	//       Author: Brian A Cheeseman.
	//   Parameters: string			- Value of argument to send.
	// Return Value: boolean		- Successfully sent.
	// ***************************************************************************
	function sendArgument($ArgToSend)
	{
		if ($this->ALLOWED_REQUESTS["Argument"] == true) {
			if ($this->SOCKET->write("Argument $ArgToSend\n") != true) {
				return false;
			}
		}
		return true;
	}

	// ***************************************************************************
	//     Function: sendRLog()
	//       Author: Brian A Cheeseman.
	//   Parameters: None.
	// Return Value: boolean		- Successfully sent.
	// ***************************************************************************
	function sendRLog()
	{
		if ($this->ALLOWED_REQUESTS["rlog"] == true) {
			if ($this->SOCKET->write("rlog\n") != true) {
				return false;
			}
		}
		return true;
	}

	// ***************************************************************************
	//     Function: sendDirectory()
	//       Author: Brian A Cheeseman.
	//   Parameters: string			- Directory to pass to the directory command.
	// Return Value: boolean		- Successfully sent.
	// ***************************************************************************
	function sendDirectory($Directory)
	{
		if ($this->ALLOWED_REQUESTS["Directory"] == true) {
			if (strncmp($Directory, "/", 1) == 0) {
				$Directory = substr($Directory, 1);
			}
			if ($this->SOCKET->write("Directory $Directory\n") == true) {
				$Line = $this->CVS_REPOSITORY;
				if ($Directory != ".") {
					$Line .= "/" . $Directory;
				}
				if ($this->SOCKET->write("$Line\n") != true) {
					return false;
				}
			} else {
				return false;
			}
		}
		return true;
	}

	// ***************************************************************************
	//     Function: sendStaticDirectory()
	//       Author: Brian A Cheeseman.
	//   Parameters: None.
	// Return Value: boolean		- Successfully sent.
	// ***************************************************************************
	function sendStaticDirectory()
	{
		if ($this->ALLOWED_REQUESTS["Static-directory"] == true) {
			if ($this->SOCKET->write("Static-directory\n") != true) {
				return false;
			}
		}
		return true;
	}

	// ***************************************************************************
	//     Function: sendEntry()
	//       Author: Brian A Cheeseman.
	//   Parameters: string			- $Name - Name of the file.
	//               string			- $Version - Version of the file.
	//               string			- $Conflict -
	//               string			- $Options - Options for the entry line.
	//               string			- $TagOrDate - Another method of identifying the version.
	// Return Value: boolean		- Successfully sent.
	// ***************************************************************************
	function sendEntry($Name = "", $Version = "", $Conflict = "", $Options = "", $TagOrDate = "")
	{
		if ($this->ALLOWED_REQUESTS["Entry"] == true) {
			if (strrpos($Name, "/") > -1) {
				$FName = substr($Name, strrpos($Name, "/")+1);
			} else {
				$FName = $Name;
			}

			if ($this->SOCKET->write("Entry /$FName/$Version/$Conflict/$Options/$TagOrDate\n") != true) {
				return false;
			}
		}
		return true;
	}

	// ***************************************************************************
	//     Function: sendUnchanged()
	//       Author: Brian A Cheeseman.
	//   Parameters: string			- $Name - Name of the file.
	// Return Value: boolean		- Successfully sent.
	// ***************************************************************************
	function sendUnchanged($Name)
	{
		if ($this->ALLOWED_REQUESTS["Unchanged"] == true) {
			$SlashPos = strrpos($Name, "/");
			if ($SlashPos !== false) {
				$BaseFileName = substr($Name, $SlashPos+1);
			} else {
				$BaseFileName = $Name;
			}

			if ($this->SOCKET->write("Unchanged $BaseFileName\n") != true) {
				return false;
			}
		}
		return true;
	}

	// ***************************************************************************
	//     Function: sendAnnotate()
	//       Author: Brian A Cheeseman.
	//   Parameters: None.
	// Return Value: boolean		- Successfully sent.
	// ***************************************************************************
	function sendAnnotate()
	{
		if ($this->ALLOWED_REQUESTS["annotate"] == true) {
			if ($this->SOCKET->write("annotate\n") != true) {
				return false;
			}
		}
		return true;
	}

	// ***************************************************************************
	//     Function: sendExpandModules()
	//       Author: Brian A Cheeseman.
	//   Parameters: None.
	// Return Value: boolean		- Successfully sent.
	// ***************************************************************************
	function sendExpandModules()
	{
		if ($this->ALLOWED_REQUESTS["expand-modules"] == true) {
			if ($this->SOCKET->write("expand-modules\n") != true) {
				return false;
			}
		}
		$this->processResponse();
		return true;
	}

	// ***************************************************************************
	//     Function: sendExportFile()
	//       Author: Brian A Cheeseman.
	//   Parameters: None.
	// Return Value: boolean		- Successfully sent.
	// ***************************************************************************
	function sendExportFile()
	{
		if ($this->ALLOWED_REQUESTS["export"] == true) {
			if ($this->SOCKET->write("export\n") != true) {
				return false;
			}
		}
		$this->processResponse();

		// Here the first line is the length, the remaining (upto the 'ok')
		// is the content of the file.
		if ($this->FINAL_RESPONSE) {
			$ContentLength = $this->SOCKET->readLine();
			$CharsToGo = $ContentLength;
			while($CharsToGo > 0){
				$Buffer = $this->SOCKET->read($CharsToGo);
				$this->FILECONTENTS .= $Buffer;
				$CharsToGo -= strlen($Buffer);
			}
			$ReadLine = $this->SOCKET->readLine();
			return true;
		} else {
			return false;
		}
	}

	// ***************************************************************************
	//     Function: sendCO()
	//       Author: Brian A Cheeseman.
	//   Parameters: None.
	// Return Value: boolean		- Successfully sent.
	// ***************************************************************************
	function sendCO()
	{
		if ($this->ALLOWED_REQUESTS["co"] == true) {
		    if ($this->SOCKET->write("co\n") != true) {
		        return false;
		    }
		}
		$this->processResponse();

		// Here the first line is the length, the remaining (upto the 'ok')
		// is the content of the file.
		if ($this->FINAL_RESPONSE) {
			$ContentLength = $this->SOCKET->readLine();
			$CharsToGo = $ContentLength;
			while($CharsToGo > 0){
				$Buffer = $this->SOCKET->read($CharsToGo);
				$this->FILECONTENTS .= $Buffer;
				$CharsToGo -= strlen($Buffer);
			}
			$ReadLine = $this->SOCKET->readLine();
			return true;
		} else {
			return false;
		}
	}

	/**
	* Helper Methods.
	**/

	// ***************************************************************************
	//     Function: RLogDirectory()
	//       Author: Brian A Cheeseman.
	//   Parameters: string			- Directory to get the RLog for.
	// Return Value: boolean		- Were we successful.
	// ***************************************************************************
	function RLog($Folder)
	{
		if (!($this->INITIALISED)) {
			$this->sendCVSROOT();
			$this->sendValidResponses();
			$this->sendValidRequests();
			$this->INITIALISED = true;
		}

		if (!$this->sendUseUnchanged()) {
		    return false;
		}

		if (strncmp($Folder, "/", 1) == 0) {
				$Directory = substr($Folder, 1);
		} else {
			$Directory = $Folder;
		}

		if (!$this->sendArgument($Directory)) {
			return false;
		}

		if (!$this->sendRLog()) {
			return false;
		}

		$this->processResponse();

		if (!($this->FINAL_RESPONSE)) {
			return $this->RLog(substr($Folder, 1));
		}

		if ($Folder == "") {
			$Folder = "/";
		}

		$DirCount = -1;
		$FileCount = -1;
		$SeenFolders = "";
		$CurrentDecode = 0;
		$FileRevision = -1;
		$CurrentRevision = "";
		$PreviousRevision = "";
		$LineProcessed = false;
		if ($this->FINAL_RESPONSE) {
			$Responses = explode("\n", $this->MESSAGE_CONTENT);
			// Iterate through each line.
			foreach ($Responses as $Line) {
				$LineProcessed = false;
				// Are we dealing with a file or a folder?
				if (strncmp($Line, "RCS file: ", 10) == 0) {
					// We have the file/dir name, so we can now determine what we are dealing with.
					$TempLine = substr($Line, 10+strlen($this->CVS_REPOSITORY.$Folder));
					if (strncmp($TempLine, "/", 1) == 0) {
						$TempLine = substr($TempLine, 1);
					}
					if (($SlashPos = strpos($TempLine, "/")) > 0) {
						// We have a folder.
						$FolderName = substr($TempLine, 0, $SlashPos);
						if (strpos($SeenFolders, $FolderName) === false) {
							$DirCount++;
							$this->FOLDERS[$DirCount]["Name"] = $FolderName;
							$SeenFolders .= " " . $FolderName;
							$CurrentDecode = 1;
							$CurrentRevision = "";
							$LineProcessed = true;
						}
					} else {
						// We have a file.
						$FileCount++;
						$FileName = substr($TempLine, 0, -2);
						$this->FILES[$FileCount]["Name"] = $FileName;
						$CurrentDecode = 2;
						$LineProcessed = true;

						// Start by initialising the array.
						$this->FILES[$FileCount]["Head"] = "";
						$this->FILES[$FileCount]["Branch"] = "";
						$this->FILES[$FileCount]["Locks"] = "";
						$this->FILES[$FileCount]["Access"] = "";
						$this->FILES[$FileCount]["KeywordSubst"] = "";
						$this->FILES[$FileCount]["TotalRevs"] = "";
						$this->FILES[$FileCount]["SelectedRevs"] = "";
						$this->FILES[$FileCount]["Description"] = "";
					}
				}
				// Lets continue, but only if we have a CurrentDecode type of 2 (ie a file).
				if ($CurrentDecode == 2) {
					// Process for the remaining file attributes.

					// Head version of file.
					if (strncmp($Line, "head:", 5) == 0) {
						$this->FILES[$FileCount]["Head"] = trim(substr($Line, 6));
						$LineProcessed = true;
					}

					// Default branch.
					if (strncmp($Line, "branch:", 7) == 0) {
						$this->FILES[$FileCount]["Branch"] = trim(substr($Line, 8));
						//$this->FILES[$FileCount]["Branch"] = (strlen($Line) > 7) ? trim(substr($Line, 8)) : "Main";
						$LineProcessed = true;
					}

					// Locking Mechanism.
					if (strncmp($Line, "locks:", 6) == 0) {
						$this->FILES[$FileCount]["Locks"] = trim(substr($Line, 7));
						$LineProcessed = true;
					}

					// Access list.
					if (strncmp($Line, "access list:", 12) == 0) {
						$this->FILES[$FileCount]["Access"] = trim(substr($Line, 13));
						$LineProcessed = true;
					}

					// Process the symbolic names.
					if (strncmp($Line, "symbolic names:", 15) == 0) {
						$LineProcessed = true;
					}

					if (strncmp($Line, "\t", 1) == 0) {
						$TempLine = substr($Line, 1);
						$SymbolName = trim(substr($TempLine, 0, strpos($TempLine, ":")));
						$SymbolValue = trim(substr($TempLine, strpos($TempLine, ":")+1));
						$this->FILES[$FileCount]["Symbols"]["$SymbolName"] = $SymbolValue;
						$this->FILES[$FileCount]["Symbols"]["$SymbolValue"] = $SymbolName;
						$LineProcessed = true;
					}

					// Process the Keyword Substitution.
					if (strncmp($Line, "keyword substitution:", 21) == 0) {
						$this->FILES[$FileCount]["KeywordSubst"] = trim(substr($Line, 22));
						$LineProcessed = true;
					}

					// Process the Total Revisions.
					if (strncmp($Line, "total revisions:", 16) == 0) {
						$TempLine = substr($Line, 17);
						$this->FILES[$FileCount]["TotalRevs"] = trim(substr($TempLine, 0, strpos($TempLine, ";")));
						$this->FILES[$FileCount]["SelectedRevs"] = trim(substr($TempLine, strpos($TempLine, ";")+22));
						$LineProcessed = true;
					}

					// Process the description.
					if (strncmp($Line, "description:", 12) == 0) {
						$this->FILES[$FileCount]["Description"] = trim(substr($Line, 13));
						$LineProcessed = true;
					}

					// Process the individual revision information.
					if (strncmp($Line, "-------------", 13) == 0) {
						$LineProcessed = true;
					}

					// Get this revision number.
					if (strncmp($Line, "revision", 8) == 0) {
						$CurrentRevision = substr($Line, 9);
						$this->FILES[$FileCount]["Revisions"]["$CurrentRevision"]["Revision"] = $CurrentRevision;
						$this->FILES[$FileCount]["Revisions"]["$CurrentRevision"]["LogMessage"] = "";
						
						// hack: set branches default for current version
						$this->FILES[$FileCount]["Revisions"]["$CurrentRevision"]["Branches"] = "Main";

						if ($PreviousRevision != "") {
							$this->FILES[$FileCount]["Revisions"]["$PreviousRevision"]["PrevRevision"] = $CurrentRevision;
						}

						// temporary solution for initial file revision not having a previous version to create a diff
						if ($CurrentRevision == "1.1") {
							$this->FILES[$FileCount]["Revisions"]["$CurrentRevision"]["PrevRevision"] = "";
						}

						$PreviousRevision = $CurrentRevision;
						$LineProcessed = true;
					}

					// Get the Date, Author, State and Lines of this revision.
					if (strncmp($Line, "date:", 5) == 0) {
						$Segment = explode(";", $Line);
						foreach($Segment as $Part){
							$SepPos = trim(strpos($Part, ":"));
							$Name = trim(substr($Part, 0, $SepPos));
							$Value = trim(substr($Part, $SepPos+1));
							$this->FILES[$FileCount]["Revisions"]["$CurrentRevision"]["$Name"] = $Value;
						}
						$LineProcessed = true;
					}

					// Get the current revisions branch.
					if (strncmp($Line, "branches:", 9) == 0) {
						$this->FILES[$FileCount]["Revisions"]["$CurrentRevision"]["Branches"] = trim(substr($Line, 10));
						//$this->FILES[$FileCount]["Revisions"]["$CurrentRevision"]["Branches"] = (strlen($Line) > 9) ? trim(substr($Line, 10)) : "Main";
						$LineProcessed = true;
					}

					// Deal with the new file seperator.
					if (strncmp($Line, "=============", 13) == 0) {
						$CurrentDecode = 0;
						$LineProcessed = true;
					}

					// Get any lines not already processed and assume they are the log message.
					if (!$LineProcessed) {
						if (strlen($this->FILES[$FileCount]["Revisions"]["$CurrentRevision"]["LogMessage"]) > 0) {
							$this->FILES[$FileCount]["Revisions"]["$CurrentRevision"]["LogMessage"] .= "\n";
						}
						$Line = trim($Line);
						if ($Line != "") {
							$this->FILES[$FileCount]["Revisions"]["$CurrentRevision"]["LogMessage"] .= trim($Line);
						}
					}
				}
			}
		}
	}

	// ***************************************************************************
	//     Function: Annotate()
	//       Author: Brian A Cheeseman.
	//   Parameters: string			- Directory to get the RLog for.
	// Return Value: boolean		- Were we successful.
	// ***************************************************************************
	function Annotate($Name, $Revision = "")
	{
		if (!($this->INITIALISED)) {
			$this->sendCVSROOT();
			$this->sendValidResponses();
			$this->sendValidRequests();
			$this->INITIALISED = true;
		}

		if (!$this->sendUseUnchanged()) {
			return false;
		}

		if (!$this->sendArgument("--")) {
			return false;
		}

		$SlashPos = strrpos($Name, "/");
		if ($SlashPos > -1) {
			$Directory = substr($Name, 0, $SlashPos);
		} else {
			$Directory = "/";
		}

		if (!$this->sendDirectory($Directory)) {
			return false;
		}

		if (!$this->sendStaticDirectory()) {
			return false;
		}

		if (!$this->sendEntry($Name, $Revision)) {
			return false;
		}

		if (!$this->sendUnchanged($Name)) {
			return false;
		}

		if (!$this->sendDirectory(".")) {
			return false;
		}

		if (strncmp($Name, "/", 1) == 0) {
			$Arg = substr($Name, 1);
		} else {
			$Arg = $Name;
		}

		if (!$this->sendArgument($Arg)) {
			return false;
		}

		if (!$this->sendAnnotate()) {
			return false;
		}

		$this->processResponse();

		if ($this->FINAL_RESPONSE) {
			$Counter = 0;
			$Responses = explode("\n", $this->MESSAGE_CONTENT);
			// Iterate through each line.
			foreach ($Responses as $Line) {
				$this->ANNOTATION[$Counter]["Revision"] = strtok($Line, "(");
				$this->ANNOTATION[$Counter]["Author"] = strtok(" ");
				$this->ANNOTATION[$Counter]["Date"] = strtok(")");
				$this->ANNOTATION[$Counter]["Line"] = substr(strtok("\n"), 2);
				$Counter++;
			}
		}
		return true;
	}

	// ***************************************************************************
	//     Function: ExportFile()
	//       Author: Brian A Cheeseman.
	//   Parameters: string			- Name of file to export.
	// 				 integer		- Date/Time of revision to export.
	// Return Value: boolean		- Were we successful.
	// ***************************************************************************
	function ExportFile($FileName, $DateTime)
	{
		if (!($this->INITIALISED)) {
			$this->sendCVSROOT();
			$this->sendValidResponses();
			$this->sendValidRequests();
			$this->INITIALISED = true;
		}
		
		$this->FILECONTENTS = "";

		if (strncmp($FileName, "/", 1) == 0) {
			$FName = substr($FileName, 1);
		} else {
			$FName = $FileName;
		}

		if (!$this->sendUseUnchanged()) {
			return false;
		}

		if (!$this->sendArgument($FName)) {
			return false;
		}

		if (!$this->sendDirectory(".")) {
			return false;
		}

		if (!$this->sendExpandModules()) {
			return false;
		}

		if (!$this->sendArgument("-N")) {
			return false;
		}

		if (!$this->sendArgument("-D")) {
			return false;
		}

		if (!$this->sendArgument(strftime("%d %b %Y %H:%M:%S -0000", $DateTime))) {
			return false;
		}

		if (!$this->sendArgument("--")) {
			return false;
		}

		if (!$this->sendArgument($FName)) {
			return false;
		}

		if (!$this->sendDirectory(".")) {
			return false;
		}

		if (!$this->sendExportFile()) {
			return false;
		}

		return true;
	}
	
	// ***************************************************************************
	//     Function: getModuleList()
	//       Author: Brian Cheeseman.
	//   Parameters: None.
	// Return Value: Array			- contains list of module names and paths
	// ***************************************************************************
	function getModuleList()
	{
		// Create our resultant Array.
		$Results = array();
	
		// Save the current FILECONTENTS just in case.
		$TempFileContents = $this->FILECONTENTS;
		
		// Get a copy of the current modules definition file.
		$this->ExportFile("/CVSROOT/modules", gmmktime());
		$Lines = explode("\n", $this->FILECONTENTS);
		
		// Process each line of the modules file looking for module definitions.
		foreach ($Lines as $Line) {
			if (strncmp($Line, '#', 1) != 0) {
				// Process the line.
				$Elements = explode(" ", $Line);
				if (count($Elements) > 1) {
					$Results[$Elements[0]] = $Elements[1];
				}
			}
		}
		
		// Restore the saved FILECONTENTS.
		$this->FILECONTENTS = $TempFileContents;
		
		// Return our work.
		return $Results;
	}

	// ***************************************************************************
	//     Function: getFileDiff()
	//       Author: Brian Cheeseman.
	//   Parameters: string			- Name of file to diff.
	// 				 int			- Revision 1.
	//				 int			- Revision 2.
	// Return Value: void			- none.
	// ***************************************************************************
	function getFileDiff($FileName, $Revision1, $Revision2)
	{
		if (!($this->INITIALISED)) {
			$this->sendValidResponses();
			$this->sendValidRequests();
			$this->INITIALISED = true;
		}

		if (!$this->sendUseUnchanged()) {
		    return false;
		}

		if (!$this->sendCVSROOT()) {
		    return false;
		}
		
		if (!$this->sendArgument("-r")) {
		    return false;
		}
		
		if (!$this->sendArgument($Revision1)) {
		    return false;
		}
	
		if (!$this->sendArgument("-r")) {
		    return false;
		}
		
		if (!$this->sendArgument($Revision2)) {
		    return false;
		}
	
		if (!$this->sendArgument("--")) {
		    return false;
		}
		
		$dir = substr($FileName, 0, strrpos($FileName, "/"));
		$file = substr($FileName, strrpos($FileName, "/")+1);
		if (!$this->sendDirectory($dir)) {
		    return false;
		}
	
		if (!$this->sendEntry($file, $Revision2, "", "", "")) {
		    return false;
		}
		
		if (!$this->sendUnchanged($file)) {
		    return false;
		}
		
		if (!$this->sendArgument($file)) {
		    return false;
		}
		
		if ($this->SOCKET->write("diff\n") != true) {
		    return false;
		}
		
		$this->processResponse();

		return $this->MESSAGE_CONTENT;
	}

	// ***************************************************************************
	//     Function: debug()
	//       Author: Sijis Aviles.
	//   Parameters: string			- String to output.
	// 				 string			- (optional) options: var_dump, both, print_r
	// Return Value: void			- none.
	// ***************************************************************************
	function debug($foo, $bar = "")
	{
		echo "<pre>";
		switch($bar){
			case "var_dump":
				echo var_dump($foo);
				break;
			case "both":
				echo var_dump($foo);
				echo print_r($foo);
				break;
			case "print_r":
				echo print_r($foo);
			default:
				echo $foo;
		}
		echo '</pre>';
	}

}

?>
