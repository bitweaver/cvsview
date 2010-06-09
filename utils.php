<?php

/**
 * This source code is distributed under the terms as layed out in the
 * GNU General Public License.
 *
 * Purpose: To provide utility functions for the phpCVSViewer.
 *
 * @author Brian A Cheeseman <bcheesem@users.sourceforge.net>
 * @version $Id$
 * @copyright 2003-2005 Brian A Cheeseman
 **/

function CalculateDateDiff($DateEarlier, $DateLater)
{
	$date['date_diff'] = $DateLater - $DateEarlier;
	$date['seconds'] = $date['date_diff'];
	$date['minutes'] = floor($date['seconds']/60);
	$date['hours'] = floor($date['minutes']/60);
	$date['days'] = floor($date['hours']/24);
	$date['weeks'] = floor($date['days']/7);
	$date['years'] = floor($date['days']/365);

	// displays seconds
	if ($date['seconds'] > 0) {
	    $Result = $date['seconds'] .' ';
		$Result .= ($date['date_diff'] > 1)? 'Seconds' : 'Second';
	}

	// displays minutes
	if ($date['minutes'] > 0) {
	    $Result = $date['minutes'].' ';
		$Result .= ($date['minutes'] > 1)? 'Minutes' : 'Minute';
	}

	// displays hours, then minutes
	if ($date['hours'] > 0) {
	    $Result = $date['hours'].' ';
		$Result .= ($date['hours'] > 1)? 'Hours' : 'Hour';
		$date['minutes'] = $date['minutes'] % 60;
		if ($date['minutes'] > 0) {
		    $Result .= ', '.$date['minutes'].' ';
			$Result .= ($date['minutes'] > 1)? 'Minutes' : 'Minute';
		}
	}

	// displays days, then hours
	if ($date['days'] > 0) {
	    $Result = $date['days'] . ' ';
		$Result .= ($date['days'] > 1)? 'Days' : 'Day';
		$date['hours'] = $date['hours'] % 24;
		if ($date['hours'] > 0) {
		    $Result .= ', '.$date['hours'].' ';
			$Result .= ($date['hours'] > 1)? 'Hours' : 'Hour';
		}
	}

	// displays weeks, then days
	if ($date['weeks'] > 0) {
	    $Result = $date['weeks'] . ' ';
		$Result .= ($date['weeks'] > 1)? 'Weeks' : 'Week';
		$date['days'] = $date['days'] % 7;
		if ($date['days'] > 0) {
		    $Result .= ', '.$date['days'].' ';
			$Result .= ($date['days'] > 1)? 'Days' : 'Day';
		}
	}

	// displays years, then weeks
	if ($date['years'] > 0) {
		$Result = $date['years'] . ' ';
		$Result .= ($date['years'] > 1)? 'Years' : 'Year';
		$date['weeks'] = $date['weeks'] % 52;
		if ($date['weeks'] > 0) {
		    $Result .= ', '.$date['weeks'].' ';
			$Result .= ($date['weeks'] > 1)? 'Weeks' : 'Week';
		}
	}
	return $Result;
}

function ImplodeToPath($Dirs, $Seperator, $Number)
{
	$RetVal = "";
	for ($Counter = 0; $Counter <= $Number; $Counter++)
	{
		if ($Dirs[$Counter] != "") {
			$RetVal .= $Seperator . $Dirs[$Counter];
		}
	}
	return $RetVal;
}

function InsertIntoArray($Array, $Value, $Position)
{
	if (!is_array($Array)) {return $Array;}
	$Last = array_splice($Array, $Position);
	$Array[] = $Value;
	$Array = array_merge($Array, $Last);
	return $Array;
}

?>
