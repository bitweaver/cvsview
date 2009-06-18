<?php

/**
 * This source code is distributed under the terms as layed out in the
 * GNU General Public License.
 *
 * Purpose: To store the configuration for this instance of phpCVSView
 *
 * @author Brian A Cheeseman <bcheesem@users.sourceforge.net>
 * @version $Id: config.php,v 1.5 2009/06/18 06:15:24 lsces Exp $
 * @copyright 2003-2005 Brian A Cheeseman
 **/

// CVSROOT configuration.
/* CMS Source Repository
 Add your own local CVS link here
  */
$config['cvs']['CMS']['server'] = "10.0.0.5";
$config['cvs']['CMS']['cvsroot'] = "/CVSROOT/Projects";
$config['cvs']['CMS']['username'] = "guest";
$config['cvs']['CMS']['passwd'] = "";
$config['cvs']['CMS']['mode'] = "pserver";
$config['cvs']['CMS']['description'] = "CMS CVS Repository Viewer";
$config['cvs']['CMS']['html_title'] = "CMS Source Code Library";
$config['cvs']['CMS']['html_header'] = "CMS Source Code Library";

/* bitweaver Source Repository */
$config['cvs']['bitweaver']['server'] = "bitweaver.cvs.sourceforge.net";
$config['cvs']['bitweaver']['cvsroot'] = "/cvsroot/bitweaver";
$config['cvs']['bitweaver']['username'] = "anonymous";
$config['cvs']['bitweaver']['passwd'] = "";
$config['cvs']['bitweaver']['mode'] = "pserver";
$config['cvs']['bitweaver']['description'] = "bitweaver CVS Repository Viewer";
$config['cvs']['bitweaver']['html_title'] = "bitweaver Source Code Library";
$config['cvs']['bitweaver']['html_header'] = "bitweaver Source Code Library";

// Default CVSROOT configuration to use.
$config['default_cvs'] = "bitweaver";

// Settings for TAR creation.
$config['TempFileLocation'] = "/var/tmp";

// Settings for Output Cache.
$config['Cache']['Enable'] = true;
$config['Cache']['Location'] = "/var/tmp/phpCVSViewCache";

?>
