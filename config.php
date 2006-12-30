<?php

/**
 * This source code is distributed under the terms as layed out in the
 * GNU General Public License.
 *
 * Purpose: To store the configuration for this instance of phpCVSView
 *
 * @author Brian A Cheeseman <bcheesem@users.sourceforge.net>
 * @version $Id: config.php,v 1.2 2006/12/30 14:40:57 lsces Exp $
 * @copyright 2003-2005 Brian A Cheeseman
 **/

// CVSROOT configuration.
/* CMS Source Repository */
$config['cvs']['CMS']['server'] = "lscserver";
$config['cvs']['CMS']['cvsroot'] = "/CVSROOT/CMS";
$config['cvs']['CMS']['username'] = "guest";
$config['cvs']['CMS']['password'] = "";
$config['cvs']['CMS']['mode'] = "pserver";
$config['cvs']['CMS']['description'] = "CMS CVS Repository Viewer";
$config['cvs']['CMS']['html_title'] = "CMS Source Code Library";
$config['cvs']['CMS']['html_header'] = "CMS Source Code Library";

/* bitweaver Source Repository */
$config['cvs']['bitweaver']['server'] = "bitweaver.cvs.sourceforge.net";
$config['cvs']['bitweaver']['cvsroot'] = "/cvsroot/bitweaver";
$config['cvs']['bitweaver']['username'] = "anonymous";
$config['cvs']['bitweaver']['password'] = "";
$config['cvs']['bitweaver']['mode'] = "pserver";
$config['cvs']['bitweaver']['description'] = "bitweaver CVS Repository Viewer";
$config['cvs']['bitweaver']['html_title'] = "bitweaver Source Code Library";
$config['cvs']['bitweaver']['html_header'] = "bitweaver Source Code Library";

// Default CVSROOT configuration to use.
$config['default_cvs'] = "bitweaver";

// Settings for TAR creation.
$config['TempFileLocation'] = "c:/tmp";

// Settings for Output Cache.
$config['Cache']['Enable'] = true;
$config['Cache']['Location'] = "c:/tmp/phpCVSViewCache";

?>
